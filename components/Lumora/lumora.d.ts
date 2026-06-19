declare type LumoraViewportMode = {
    aspectRatio: number;
    name: string
}

declare type LumoraViewportDimension = {
    maxHeight: number;
    maxWidth: number;
    targetWidth: number;
    targetHeight: number;
    targetAspectRatio: number;
    width: number;
    height: number;
}

declare type LumoraViewportResizeListener = (dimension: LumoraViewportDimension) => void;
