declare type BindingValidator<T> = (value: T, context: HTMLElement) => Promise<string|null> | string | null;
declare type BindingOnChange<T> = (value: T, context: HTMLElement) => void;