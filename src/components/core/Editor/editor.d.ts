declare type EditorViewportMode = {
    aspectRatio: number;
    name: string
}

declare type EditorViewportDimension = {
    maxHeight: number;
    maxWidth: number;
    targetWidth: number;
    targetHeight: number;
    targetAspectRatio: number;
    width: number;
    height: number;
}

declare type EditorViewportResizeListener = (dimension: EditorViewportDimension) => void;
