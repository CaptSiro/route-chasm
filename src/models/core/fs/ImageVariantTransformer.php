<?php

namespace models\core\fs;

use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use components\layout\Grid\description\GridDescription;
use components\layout\Grid\GridLayoutFactory;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\DatabaseAction;
use core\database\sql\Model;
use core\database\sql\ModelCache;
use core\database\sql\query\Query;
use core\database\sql\Table;
use core\forms\description\NumberField;
use core\forms\description\select\Select;
use core\forms\description\TextField;
use core\fs\variants\FileVariant;
use core\fs\variants\FileVariantTransformer;
use core\fs\variants\ImageVariant;
use core\guards\Guard;
use core\guards\NumberGuard;
use core\guards\StringGuard;
use core\RouteChasmEnvironment;
use core\view\View;
use GdImage;

#[Grid(proxy: new ImageVariantProxy())]
#[Database(App::DATABASE)]
#[Table('core_fs_image_variant')]
class ImageVariantTransformer extends Model implements FileVariantTransformer {
    use ModelCache;



    public const FUNCTION_FIT = 'fit';
    public const FUNCTION_SCALE = 'scale';
    public const FUNCTION_CROP = 'crop';

    public const FUNCTIONS = [
        'fit' => 'Fit',
        'scale' => 'Scale',
        'crop' => 'Crop',
    ];



    public static function getGridLayoutFactory(): GridLayoutFactory {
        $description = GridDescription::extract(self::class);
        $description->addColumn(
            ImageVariantProxy::COLUMN_SIZE,
            new GridColumn('Size', '128px')
        );
        return $description;
    }

    public static function fromTransformer(string $transformer): ?static {
        self::modelCache_loadAll(fn($x) => $x->transformer);

        return self::modelCache_get($transformer)
            ?? self::first(where: Query::infer('transformer = ?', $transformer));
    }

    public static function createTransformer(
        string $transformer,
        int $width,
        int $height,
        string $function = self::FUNCTION_FIT,
        float $quality = 1
    ): static {
        return static::create([
            'transformer' => $transformer,
            'width' => $width,
            'height' => $height,
            'function' => $function,
            'quality' => $quality,
        ]);
    }



    #[Column('id_fs_image_variant', type: Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;

    #[GridColumn("Name")]
    #[TextField("Name")]
    #[Column(type: Column::TYPE_STRING)]
    public string $transformer;

    #[Column(type: Column::TYPE_INTEGER)]
    public int $version = 0;

    #[NumberField("Quality (0 worst, 1 best)", step: 0.01)]
    #[Column(type: Column::TYPE_FLOAT)]
    public float $quality = 1;

    #[GridColumn(template: '96px')]
    #[Select(self::FUNCTIONS)]
    #[Column(type: Column::TYPE_STRING)]
    public string $function;

    #[NumberField]
    #[Column(type: Column::TYPE_INTEGER)]
    public int $width;

    #[NumberField]
    #[Column(type: Column::TYPE_INTEGER)]
    public int $height;



    // Model
    public function getHumanIdentifier(): string {
        return $this->transformer;
    }

    public function save(): DatabaseAction|View {
        $guards = [
            StringGuard::nonEmpty($this->transformer, 'transformer', 'Name must not be empty'),
            StringGuard::satisfiesRegex(
                $this->transformer,
                "/[a-zA-Z0-9\-_]*/",
                'transformer',
                'Name must only contain simple characters (A-Z or numbers or - or _)'
            ),
            NumberGuard::inRange(
                $this->width, -1, 8000,
                'width'
            ),
            NumberGuard::inRange(
                $this->height, -1, 8000,
                'height'
            ),
            NumberGuard::inRangeFloat(
                $this->quality, 0, 1,
                'quality', 'Quality must be in range from 0 to 1'
            ),
        ];

        if ($result = Guard::testGroup($guards)) {
            return $result;
        }

        $this->version++;

        return parent::save();
    }



    public function getSize(): string {
        $size = $this->width > 0
            ? $this->width
            : RouteChasmEnvironment::CHAR_INFINITY;

        $size .= 'x';

        $size .= $this->height > 0
            ? $this->height
            : RouteChasmEnvironment::CHAR_INFINITY;

        return $size;
    }

    public function getQuality(): float {
        return max(0.0, min($this->quality, 1.0));
    }



    // FileVariantTransformer
    public function getFileVariant(): FileVariant {
        return ImageVariant::getInstance();
    }

    public function getTransformer(): string {
        return $this->transformer;
    }

    public function getTransformerLabel(): string {
        return $this->transformer .' ('
            . $this->getSize() .', '
            . ucfirst($this->function) .')';
    }

    public function createVariantFileName(string $filePath, ?string $transformer = null, mixed $version = null): string {
        return $filePath . '_' . ($transformer ?? $this->transformer) . '-v' . ($version ?? $this->version);
    }

    public function supports(File $file): bool {
        return $file->isTypeOf(File::TYPE_IMAGE);
    }

    public function transform(File $file): string {
        $path = $file->getRealPath();
        $variant = $this->createVariantFileName($path);
        if (file_exists($variant)) {
            return $variant;
        }

        foreach (glob($this->createVariantFileName($path, version: '*')) as $variantFile) {
            unlink($variantFile);
        }

        return match ($this->function) {
            'scale' => $this->transformScale($file, $variant),
            'crop' => $this->transformCrop($file, $variant),
            'fit' => $this->transformFit($file, $variant),
            default => $path,
        };
    }

    protected function positiveDimensions(): bool {
        return $this->width >= 0 && $this->height >= 0;
    }

    protected function createImageHandle(File $file): false|GdImage {
        $path = $file->getRealPath();

        return match ($file->type) {
            File::TYPE_IMAGE_PNG => imagecreatefrompng($path),
            File::TYPE_IMAGE_JPEG => imagecreatefromjpeg($path),
            File::TYPE_IMAGE_GIF => imagecreatefromgif($path),
            File::TYPE_IMAGE_AVIF => imagecreatefromavif($path),
            File::TYPE_IMAGE_BMP => imagecreatefrombmp($path),
            File::TYPE_IMAGE_WEBP => imagecreatefromwebp($path),
        };
    }

    protected function writeImage(GdImage $image, File $file, string $variant): void {
        switch ($file->type) {
            case File::TYPE_IMAGE_PNG: {
                $quality = ($this->getQuality() - 1) * 9;
                imagepng($image, $variant, $quality);
                break;
            }

            case File::TYPE_IMAGE_JPEG: {
                $quality = $this->getQuality() * 100;
                imagejpeg($image, $variant, $quality);
                break;
            }

            case File::TYPE_IMAGE_GIF: {
                imagegif($image, $variant);
                break;
            }

            case File::TYPE_IMAGE_AVIF: {
                $quality = $this->getQuality() * 100;
                imageavif($image, $variant, $quality);
                break;
            }

            case File::TYPE_IMAGE_BMP: {
                imagebmp($image, $variant, false);
                break;
            }

            case File::TYPE_IMAGE_WEBP: {
                $quality = $this->getQuality() * 100;
                imagewebp($image, $variant, $quality);
                break;
            }
        }
    }

    protected function transformScale(File $file, string $variant): string {
        if (!$this->positiveDimensions()) {
            return $file->getRealPath();
        }

        $image = $this->createImageHandle($file);

        $transformed = imagescale(
            $image,
            $this->width,
            $this->height
        );

        $this->writeImage($transformed, $file, $variant);

        imagedestroy($transformed);
        imagedestroy($image);

        return $variant;
    }

    protected function transformCrop(File $file, string $variant): string {
        if (!$this->positiveDimensions()) {
            return $file->getRealPath();
        }

        $image = $this->createImageHandle($file);
        [$imageWidth, $imageHeight] = getimagesize($file->getRealPath());

        $width = (int) round($this->width < 0
            ? $imageWidth
            : min($imageWidth, $this->width));

        $height = (int) round($this->height < 0
            ? $imageHeight
            : min($imageHeight, $this->height));

        if ($width === $imageWidth && $height === $imageHeight) {
            $this->writeImage($image, $file, $variant);
            imagedestroy($image);
            return $variant;
        }

        $x = (int) round(($imageWidth - $width) / 2);
        $y = (int) round(($imageHeight - $height) / 2);

        $transformed = imagecrop($image, [
            "x" => $x,
            "y" => $y,
            "width" => $width,
            "height" => $height,
        ]);

        $this->writeImage($transformed, $file, $variant);

        imagedestroy($transformed);
        imagedestroy($image);

        return $variant;
    }

    protected function transformFit(File $file, string $variant): string {
        if (!$this->positiveDimensions()) {
            return $file->getRealPath();
        }

        $image = $this->createImageHandle($file);
        [$imageWidth, $imageHeight] = getimagesize($file->getRealPath());

        $factor = max(((float) $this->width) / $imageWidth, ((float) $this->height) / $imageHeight);
        $scaledWidth = (int) round($imageWidth * $factor);
        $scaledHeight = (int) round($imageHeight * $factor);

        $scaled = imagescale($image, $scaledWidth, $scaledHeight);

        $x = (int) round(($scaledWidth - $this->width) / 2);
        $y = (int) round(($scaledHeight - $this->height) / 2);

        $transformed = imagecrop($scaled, [
            "x" => $x,
            "y" => $y,
            "width" => $this->width,
            "height" => $this->height,
        ]);

        $this->writeImage($transformed, $file, $variant);

        imagedestroy($transformed);
        imagedestroy($scaled);
        imagedestroy($image);

        return $variant;
    }
}