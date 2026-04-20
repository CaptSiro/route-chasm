export type StartuhWidgetConfig = {
    builder: string,
    x: number,
    y: number,
}

declare interface StartuhBuilder<T, C extends StartuhWidgetConfig> {
    get name(): string;

    create(): T;

    build(config: C): T;
}