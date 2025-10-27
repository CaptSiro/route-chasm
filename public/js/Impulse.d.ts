declare type ImpulseListener<T> = (value: T) => any;

declare type ImpulseOptions<T> = {
    default?: T,
    pulseOnDuplicate?: boolean,
};

export declare class Impulse<I> {
    public constructor(options: ImpulseOptions<I> | undefined);

    public setPulseOnDuplicate(bool: boolean): void;

    public listen(listener: ImpulseListener<I>): Impulse<I>;

    public removeListener(listener: ImpulseListener<I>): void;

    public pulse(value: I): void;

    public value(): I;
}
