declare type LumoraEditorFileSystemApi = {
    directoryUrl: string,
    imageVariantUrl: string

    createFileUrl(hash: string, variant?: string): string | null,
    createDirectoryUrl(type?: string): string;
}