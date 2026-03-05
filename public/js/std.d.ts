declare type Opt<T> = T | undefined | null;

declare type RelativeTimestamp = {
    name: string | any,
    amount: number
}

declare type InternalServerError = {
    type: 'error',
    severity: number,
    message: string,
    file: string,
    line: number,
} | {
    type: 'exception',
    exception: string,
    message: string,
    trace: {
        file: string,
        line: number
    }[];
}

declare type SkipPredicate<T> = (item: T) => boolean;

declare class Impulse<T> {}

declare type BrowserType = "chrome" | "opera" | "firefox" | "safari" | "internet-explorer" | "edge" | "edge-chromium" | string;
