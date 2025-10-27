import { Impulse } from "../../../../public/js/Impulse";



declare type JsmlContentItem = Impulse<any> | HTMLElement | Node | string | undefined;

declare type JsmlContent = JsmlContent[] | JsmlContentItem | ArrayLike<JsmlContentItem> | HTMLCollection | undefined;

declare type JsmlAttributes = {
    [key: string]: ((event: Event) => any) | Impulse<any> | any
} & {
    style?: Partial<CSSStyleDeclaration>
};

declare type JsmlProps = JsmlAttributes | string | undefined;

declare type Jsml = {
    [key in keyof HTMLElementTagNameMap]: (props?: JsmlProps | string, content?: JsmlContent) => HTMLElementTagNameMap[key]
}