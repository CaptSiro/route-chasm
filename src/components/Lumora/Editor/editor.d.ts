declare type LumoraEditorFileSystemApi = {
    directoryUrl: string,
    imageVariantUrl: string

    createFileUrl(hash: string, variant?: string): string | null,
    createDownloadUrl(hash: string, name: string, variant?: string): string | null,
    createInfoUrl(hash: string): string,
    createDirectoryUrl(type?: string): string;
}