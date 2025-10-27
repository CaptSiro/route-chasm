declare type Opt<T> = T | undefined | null;

declare type RelativeTimestamp = {
    name: string | any,
    amount: number
}

declare type SkipPredicate<T> = (item: T) => boolean;

declare class Impulse<T> {}
