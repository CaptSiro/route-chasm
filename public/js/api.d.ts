declare type FileInfo = {
    name: string,
    extension: string,
    fileName: string,
    type: string,
    size: string,
    sizeHumanReadable: string,
    parent: string,
    icon: string,
}

declare type FileSystemApi = {
    directoryUrl: string,
    imageVariantUrl: string

    createFileUrl(hash: string, variant?: string): string | null,
    createDownloadUrl(hash: string, name: string, variant?: string): string | null,
    createInfoUrl(hash: string): string,
    createDirectoryUrl(type?: string): string;
}



declare type LocalizationApi = {
    language: string,
    title: string,
    description: string,
    releaseDate: string,
    localizations: {
        language: string,
        url: string
    }[]
}



declare type SearchApi = {
    searchFullTextUrl: string,
    searchQuery: string
}



declare type StartuhApi = {
    randomBackground: string,
}