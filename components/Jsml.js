/**
 * jsml: DOM building with JSX-like templates, components and fine-grained reactivity, no build step.
 * Usage guide (read it first, gotchas are at the top): components/Jsml.md
 *
 * Standalone: no dependency on std.js, Impulses are duck-typed.
 * Backward compatible with the original `jsml.div(props, content)` API and with `Impulse`.
 *
 * Three ways to write the same thing:
 *
 *   jsml.div("card", [jsml.h2(_, title), jsml.p(_, () => count())])      // proxy API
 *   jsml(Card, { title }, jsml.p(_, "body"))                            // h()-style (React.createElement-like)
 *   html`<${Card} title=${title}><p>body</p></>`                        // tagged template (closest to JSX)
 *
 * Reactivity is fine-grained (SolidJS style): components run once, and only functions
 * (`() => count()`) and Impulses placed in content / attributes update the DOM.
 */

// ---------------------------------------------------------------------------------------------------------------------
// Public types (JSDoc only, used by editors / `// @ts-check`)
// ---------------------------------------------------------------------------------------------------------------------

/**
 * Reactive getter returned by `signal` / `memo`. Reading it inside an effect, memo or binding subscribes to it.
 *
 * @template T
 * @typedef {() => T} Accessor
 */

/**
 * Signal writer: takes the next value or an updater `prev => next`. Returns the stored value.
 *
 * @template T
 * @typedef {(value: T | ((prev: T) => T)) => T} Writter
 */

/**
 * @template T
 * @typedef {[get: Accessor<T>, set: Writter<T>]} Signal
 */

/**
 * @template T
 * @typedef {{ equals?: ((a: T, b: T) => boolean) | false }} SignalOptions
 */

/**
 * A plain value, an accessor or an Impulse. Unwrap it with `read()`.
 *
 * @template T
 * @typedef {T | Accessor<T> | Impulse<T>} MaybeReactive
 */

/**
 * Anything that can be rendered. `null`, `undefined` and booleans render nothing, functions and Impulses are live.
 *
 * @typedef {Node | string | number | bigint | boolean | null | undefined | Impulse<any> | (() => JsmlContent) | JsmlContentList | NodeList | HTMLCollection} JsmlContent
 */

/**
 * (separate alias, an inline `JsmlContent[]` makes the JSDoc typedef circular)
 *
 * @typedef {JsmlContent[]} JsmlContentList
 */

/**
 * `"a b"`, `["a", cond && "b"]` or `{ a: true, b: () => isB() }`.
 *
 * @typedef {string | false | null | undefined | JsmlClassList | { [className: string]: MaybeReactive<unknown> }} JsmlClass
 */

/**
 * @typedef {JsmlClass[]} JsmlClassList
 */

/**
 * @typedef {string | number | false | null | undefined} JsmlStyleValue
 */

/**
 * Keys of `CSSStyleDeclaration` that are CSS properties (camelCase, converted to kebab-case).
 *
 * @typedef {{ [K in Extract<keyof CSSStyleDeclaration, string>]: CSSStyleDeclaration[K] extends string ? K : never }[Extract<keyof CSSStyleDeclaration, string>]} JsmlStyleKey
 */

/**
 * @typedef {{ [K in JsmlStyleKey]?: MaybeReactive<JsmlStyleValue> } & { [customProperty: `--${string}`]: MaybeReactive<JsmlStyleValue> }} JsmlStyleObject
 */

/**
 * @typedef {string | JsmlStyleObject | Accessor<string | JsmlStyleObject | null | undefined> | Impulse<string | JsmlStyleObject | null | undefined>} JsmlStyle
 */

/**
 * Event object with `currentTarget` narrowed to the element the listener is on.
 *
 * @template {Event} V
 * @template {EventTarget} E
 * @typedef {V & { currentTarget: E }} JsmlEvent
 */

/**
 * camelCase spellings of multi-word events, `onKeyDown` → "keydown".
 *
 * @typedef {{
 *     DblClick: "dblclick", ContextMenu: "contextmenu", AuxClick: "auxclick",
 *     KeyDown: "keydown", KeyUp: "keyup", KeyPress: "keypress", BeforeInput: "beforeinput",
 *     MouseDown: "mousedown", MouseUp: "mouseup", MouseMove: "mousemove", MouseEnter: "mouseenter",
 *     MouseLeave: "mouseleave", MouseOver: "mouseover", MouseOut: "mouseout",
 *     PointerDown: "pointerdown", PointerUp: "pointerup", PointerMove: "pointermove", PointerEnter: "pointerenter",
 *     PointerLeave: "pointerleave", PointerOver: "pointerover", PointerOut: "pointerout", PointerCancel: "pointercancel",
 *     TouchStart: "touchstart", TouchEnd: "touchend", TouchMove: "touchmove", TouchCancel: "touchcancel",
 *     DragStart: "dragstart", DragEnd: "dragend", DragEnter: "dragenter", DragLeave: "dragleave", DragOver: "dragover",
 *     FocusIn: "focusin", FocusOut: "focusout",
 *     TransitionEnd: "transitionend", AnimationStart: "animationstart", AnimationEnd: "animationend",
 *     CompositionStart: "compositionstart", CompositionEnd: "compositionend",
 * }} JsmlCamelEvents
 */

/**
 * `onClick`, `onInput`, `onKeyDown`, … and `on:exactName` for custom events.
 *
 * @template {Element} E
 * @typedef {{ [K in keyof HTMLElementEventMap as `on${Capitalize<K>}`]?: (this: E, event: JsmlEvent<HTMLElementEventMap[K], E>) => any }
 *     & { [K in keyof JsmlCamelEvents as `on${K}`]?: (this: E, event: JsmlEvent<HTMLElementEventMap[JsmlCamelEvents[K]], E>) => any }
 *     & { [custom: `on:${string}`]: (this: E, event: JsmlEvent<CustomEvent<any>, E>) => any }} JsmlEventProps
 */

/**
 * @typedef {string | number | boolean | null | undefined} JsmlAttributeValue
 */

/**
 * Every attribute may also be an accessor or an Impulse.
 *
 * @template A
 * @typedef {{ [K in keyof A]?: MaybeReactive<A[K] | null | undefined> }} JsmlReactiveAttributes
 */

/**
 * Attribute names are written as the proxy API expects them: camelCase is converted to kebab-case.
 *
 * @typedef {{
 *     id: string, title: string, lang: string, dir: "ltr" | "rtl" | "auto", hidden: boolean, inert: boolean,
 *     tabindex: number | string, role: string, slot: string, draggable: boolean | "true" | "false",
 *     contenteditable: boolean | "true" | "false" | "plaintext-only", spellcheck: boolean | "true" | "false",
 *     popover: boolean | "auto" | "manual", ariaLabel: string, ariaHidden: boolean | "true" | "false",
 *     ariaExpanded: boolean | "true" | "false", ariaControls: string, ariaDescribedby: string,
 * }} JsmlGlobalAttributes
 */

/**
 * @typedef {"button" | "checkbox" | "color" | "date" | "datetime-local" | "email" | "file" | "hidden" | "image" | "month" | "number" | "password" | "radio" | "range" | "reset" | "search" | "submit" | "tel" | "text" | "time" | "url" | "week"} JsmlInputType
 */

/**
 * @typedef {{
 *     viewBox: string, xmlns: string, width: number | string, height: number | string, x: number | string, y: number | string,
 *     cx: number | string, cy: number | string, r: number | string, rx: number | string, ry: number | string,
 *     x1: number | string, y1: number | string, x2: number | string, y2: number | string, d: string, points: string,
 *     fill: string, stroke: string, strokeWidth: number | string, strokeDasharray: string, strokeDashoffset: number | string,
 *     strokeLinecap: "butt" | "round" | "square", strokeLinejoin: "miter" | "round" | "bevel", opacity: number | string,
 *     fillOpacity: number | string, strokeOpacity: number | string, transform: string, textAnchor: "start" | "middle" | "end",
 *     dominantBaseline: string, href: string, preserveAspectRatio: string, pathLength: number | string,
 * }} JsmlSvgAttributes
 */

/**
 * Element specific attributes, by tag name.
 *
 * @typedef {{
 *     a: { href: string, target: "_self" | "_blank" | "_parent" | "_top" | (string & {}), rel: string, download: boolean | string },
 *     button: { type: "button" | "submit" | "reset", disabled: boolean, name: string, value: string | number, form: string },
 *     form: { action: string, method: "get" | "post" | "dialog", enctype: string, novalidate: boolean, target: string },
 *     img: { src: string, alt: string, width: number | string, height: number | string, loading: "eager" | "lazy", srcset: string, sizes: string },
 *     input: {
 *         type: JsmlInputType | (string & {}), name: string, value: string | number, checked: boolean, placeholder: string,
 *         disabled: boolean, readonly: boolean, required: boolean, min: number | string, max: number | string,
 *         step: number | string, minlength: number, maxlength: number, pattern: string, autocomplete: string,
 *         autofocus: boolean, multiple: boolean, accept: string, list: string, form: string, indeterminate: boolean,
 *     },
 *     label: { for: string },
 *     option: { value: string | number, selected: boolean, disabled: boolean, label: string },
 *     select: { name: string, value: string | number, disabled: boolean, multiple: boolean, required: boolean, size: number },
 *     textarea: {
 *         name: string, value: string, placeholder: string, rows: number, cols: number, disabled: boolean,
 *         readonly: boolean, required: boolean, maxlength: number, wrap: "soft" | "hard",
 *     },
 *     td: { colspan: number, rowspan: number },
 *     th: { colspan: number, rowspan: number, scope: "row" | "col" | "rowgroup" | "colgroup" },
 * }} JsmlHtmlAttributes
 */

/**
 * Tags the proxy API creates in the SVG namespace.
 *
 * @typedef {"svg" | "g" | "path" | "circle" | "ellipse" | "line" | "polyline" | "polygon" | "rect" | "use" | "defs" | "symbol"
 *     | "marker" | "mask" | "pattern" | "clipPath" | "linearGradient" | "radialGradient" | "stop" | "text" | "tspan" | "textPath"
 *     | "foreignObject" | "filter" | "feGaussianBlur" | "feOffset" | "feBlend" | "feColorMatrix" | "feMerge" | "feMergeNode"
 *     | "animate" | "animateTransform"} JsmlSvgTag
 */

/**
 * Element type created for each tag name.
 *
 * @typedef {HTMLElementTagNameMap & Pick<SVGElementTagNameMap, Exclude<JsmlSvgTag, keyof HTMLElementTagNameMap>>} JsmlTagMap
 */

/**
 * Props of the element created by tag `K`.
 *
 * @template {string} K
 * @typedef {JsmlProps<K extends keyof JsmlTagMap ? JsmlTagMap[K] : HTMLElement,
 *     K extends keyof JsmlHtmlAttributes ? JsmlHtmlAttributes[K] : K extends JsmlSvgTag ? JsmlSvgAttributes : {}>} JsmlTagProps
 */

/**
 * Props of an element. Known keys are typed, any other key is set as an attribute (`dataTestid` → `data-testid`).
 *
 *  - `class` / `className`: string | array | object map; values may be accessors
 *  - `style`: string | object | accessor
 *  - `onClick`: listener for "click"; `on:myEvent` keeps the exact name
 *  - `prop:name`: sets a DOM property, `attr:name`: sets an attribute without name conversion
 *  - `ref`: called with the element (or `{ current }` is filled)
 *
 * @template {Element} [E=any] `any` when the element is not known (`JsmlProps` alone), so the props fit every tag
 * @template [A={}]
 * @typedef {{
 *     class?: MaybeReactive<JsmlClass>,
 *     className?: MaybeReactive<JsmlClass>,
 *     style?: JsmlStyle,
 *     ref?: ((element: E) => void) | { current: E | null | undefined },
 *     children?: JsmlContent,
 * }
 *     & JsmlReactiveAttributes<JsmlGlobalAttributes & A>
 *     & JsmlEventProps<E>
 *     & { [property: `prop:${string}`]: unknown }
 *     & { [attribute: string]: unknown }} JsmlProps
 */

/**
 * `jsml.div(props?, ...content)`. Props may be a class string, or skipped: `jsml.div([children])`.
 *
 * @template {Element} E
 * @template [P=JsmlProps<E>]
 * @typedef {{
 *     (props?: P | string | null, ...content: JsmlContent[]): E,
 *     (content: Node | JsmlContent[], ...more: JsmlContent[]): E,
 * }} JsmlFactory
 */

/**
 * A component: a function of props, called once.
 *
 * @template [P={}]
 * @typedef {(props: P) => JsmlContent} JsmlComponent
 */

/**
 * Props with optional `children`, handy for component JSDoc: `@param {JsmlParentProps<{ title: string }>} props`.
 *
 * @template [P={}]
 * @typedef {P & { children?: JsmlContent }} JsmlParentProps
 */

/**
 * Type of the variadic children a component accepts (none when it has no `children` prop).
 *
 * @template P
 * @typedef {"children" extends keyof P ? P["children" & keyof P] : never} JsmlChildrenOf
 */

/**
 * Arguments after the component: props (optional when all of them are) and children.
 *
 * @template P
 * @typedef {{} extends Omit<P, "children">
 *     ? [props?: Omit<P, "children"> & Partial<Pick<P, "children" & keyof P>> | null, ...children: JsmlChildrenOf<P>[]]
 *     : [props: Omit<P, "children"> & Partial<Pick<P, "children" & keyof P>>, ...children: JsmlChildrenOf<P>[]]} JsmlComponentArgs
 */

/**
 * `jsml.Name(props, ...children)` for a component.
 *
 * @template P
 * @template R
 * @typedef {(...args: JsmlComponentArgs<P>) => R} JsmlComponentCaller
 */

/**
 * @template {Record<string, (props: any) => any>} M
 * @typedef {{ [K in keyof M]: M[K] extends (props: infer P) => infer R ? JsmlComponentCaller<P, R> : never }} JsmlComponentCallers
 */

/**
 * A `${value}` in an `html` template: content, a component, an attribute value, a handler, props to spread…
 * Templates are not type-checked, the function member only gives callbacks (`onClick=${e => …}`) an explicit `any`.
 *
 * @typedef {((...args: any[]) => any) | {} | null | undefined} JsmlTemplateValue
 */

/**
 * `jsml(tag | Component, props?, ...children)`, like `React.createElement`.
 *
 * @typedef {{
 *     <P, R>(component: (props: P) => R, ...args: JsmlComponentArgs<P>): R,
 *     <K extends keyof JsmlTagMap>(tag: K, props?: JsmlTagProps<K> | string | null, ...content: JsmlContent[]): JsmlTagMap[K],
 *     (tag: string, props?: JsmlProps | string | null, ...content: JsmlContent[]): HTMLElement,
 * }} JsmlH
 */

/**
 * `jsml.define({ Card })` registers components for `jsml.Card(...)`. It also returns typed callers:
 * `const { Card } = jsml.define({ Card })`.
 *
 * @typedef {{
 *     <M extends Record<string, (props: any) => any>>(components: M): JsmlComponentCallers<M>,
 *     <P, R>(name: string, component: (props: P) => R): JsmlComponentCaller<P, R>,
 * }} JsmlDefine
 */

/**
 * Components registered with `jsml.define` (their props are not known here, use the callers `define` returns for that).
 *
 * @typedef {{ [component: `${Uppercase<string>}${string}`]: (props?: Record<string, any> | string | null, ...children: any[]) => any }} JsmlRegistered
 */

/**
 * One factory per tag. Written out instead of a mapped type (`{ [K in keyof JsmlTagMap]: … }`), because
 * PhpStorm does not resolve the element type through a mapped type (it shows the raw `E`).
 * Generated from lib.dom.d.ts (HTMLElementTagNameMap) and JsmlSvgTag.
 *
 * @typedef {{
 *     a: JsmlFactory<HTMLAnchorElement, JsmlTagProps<"a">>,
 *     abbr: JsmlFactory<HTMLElement, JsmlTagProps<"abbr">>,
 *     address: JsmlFactory<HTMLElement, JsmlTagProps<"address">>,
 *     area: JsmlFactory<HTMLAreaElement, JsmlTagProps<"area">>,
 *     article: JsmlFactory<HTMLElement, JsmlTagProps<"article">>,
 *     aside: JsmlFactory<HTMLElement, JsmlTagProps<"aside">>,
 *     audio: JsmlFactory<HTMLAudioElement, JsmlTagProps<"audio">>,
 *     b: JsmlFactory<HTMLElement, JsmlTagProps<"b">>,
 *     base: JsmlFactory<HTMLBaseElement, JsmlTagProps<"base">>,
 *     bdi: JsmlFactory<HTMLElement, JsmlTagProps<"bdi">>,
 *     bdo: JsmlFactory<HTMLElement, JsmlTagProps<"bdo">>,
 *     blockquote: JsmlFactory<HTMLQuoteElement, JsmlTagProps<"blockquote">>,
 *     body: JsmlFactory<HTMLBodyElement, JsmlTagProps<"body">>,
 *     br: JsmlFactory<HTMLBRElement, JsmlTagProps<"br">>,
 *     button: JsmlFactory<HTMLButtonElement, JsmlTagProps<"button">>,
 *     canvas: JsmlFactory<HTMLCanvasElement, JsmlTagProps<"canvas">>,
 *     caption: JsmlFactory<HTMLTableCaptionElement, JsmlTagProps<"caption">>,
 *     cite: JsmlFactory<HTMLElement, JsmlTagProps<"cite">>,
 *     code: JsmlFactory<HTMLElement, JsmlTagProps<"code">>,
 *     col: JsmlFactory<HTMLTableColElement, JsmlTagProps<"col">>,
 *     colgroup: JsmlFactory<HTMLTableColElement, JsmlTagProps<"colgroup">>,
 *     data: JsmlFactory<HTMLDataElement, JsmlTagProps<"data">>,
 *     datalist: JsmlFactory<HTMLDataListElement, JsmlTagProps<"datalist">>,
 *     dd: JsmlFactory<HTMLElement, JsmlTagProps<"dd">>,
 *     del: JsmlFactory<HTMLModElement, JsmlTagProps<"del">>,
 *     details: JsmlFactory<HTMLDetailsElement, JsmlTagProps<"details">>,
 *     dfn: JsmlFactory<HTMLElement, JsmlTagProps<"dfn">>,
 *     dialog: JsmlFactory<HTMLDialogElement, JsmlTagProps<"dialog">>,
 *     div: JsmlFactory<HTMLDivElement, JsmlTagProps<"div">>,
 *     dl: JsmlFactory<HTMLDListElement, JsmlTagProps<"dl">>,
 *     dt: JsmlFactory<HTMLElement, JsmlTagProps<"dt">>,
 *     em: JsmlFactory<HTMLElement, JsmlTagProps<"em">>,
 *     embed: JsmlFactory<HTMLEmbedElement, JsmlTagProps<"embed">>,
 *     fieldset: JsmlFactory<HTMLFieldSetElement, JsmlTagProps<"fieldset">>,
 *     figcaption: JsmlFactory<HTMLElement, JsmlTagProps<"figcaption">>,
 *     figure: JsmlFactory<HTMLElement, JsmlTagProps<"figure">>,
 *     footer: JsmlFactory<HTMLElement, JsmlTagProps<"footer">>,
 *     form: JsmlFactory<HTMLFormElement, JsmlTagProps<"form">>,
 *     h1: JsmlFactory<HTMLHeadingElement, JsmlTagProps<"h1">>,
 *     h2: JsmlFactory<HTMLHeadingElement, JsmlTagProps<"h2">>,
 *     h3: JsmlFactory<HTMLHeadingElement, JsmlTagProps<"h3">>,
 *     h4: JsmlFactory<HTMLHeadingElement, JsmlTagProps<"h4">>,
 *     h5: JsmlFactory<HTMLHeadingElement, JsmlTagProps<"h5">>,
 *     h6: JsmlFactory<HTMLHeadingElement, JsmlTagProps<"h6">>,
 *     head: JsmlFactory<HTMLHeadElement, JsmlTagProps<"head">>,
 *     header: JsmlFactory<HTMLElement, JsmlTagProps<"header">>,
 *     hgroup: JsmlFactory<HTMLElement, JsmlTagProps<"hgroup">>,
 *     hr: JsmlFactory<HTMLHRElement, JsmlTagProps<"hr">>,
 *     html: JsmlFactory<HTMLHtmlElement, JsmlTagProps<"html">>,
 *     i: JsmlFactory<HTMLElement, JsmlTagProps<"i">>,
 *     iframe: JsmlFactory<HTMLIFrameElement, JsmlTagProps<"iframe">>,
 *     img: JsmlFactory<HTMLImageElement, JsmlTagProps<"img">>,
 *     input: JsmlFactory<HTMLInputElement, JsmlTagProps<"input">>,
 *     ins: JsmlFactory<HTMLModElement, JsmlTagProps<"ins">>,
 *     kbd: JsmlFactory<HTMLElement, JsmlTagProps<"kbd">>,
 *     label: JsmlFactory<HTMLLabelElement, JsmlTagProps<"label">>,
 *     legend: JsmlFactory<HTMLLegendElement, JsmlTagProps<"legend">>,
 *     li: JsmlFactory<HTMLLIElement, JsmlTagProps<"li">>,
 *     link: JsmlFactory<HTMLLinkElement, JsmlTagProps<"link">>,
 *     main: JsmlFactory<HTMLElement, JsmlTagProps<"main">>,
 *     map: JsmlFactory<HTMLMapElement, JsmlTagProps<"map">>,
 *     mark: JsmlFactory<HTMLElement, JsmlTagProps<"mark">>,
 *     menu: JsmlFactory<HTMLMenuElement, JsmlTagProps<"menu">>,
 *     meta: JsmlFactory<HTMLMetaElement, JsmlTagProps<"meta">>,
 *     meter: JsmlFactory<HTMLMeterElement, JsmlTagProps<"meter">>,
 *     nav: JsmlFactory<HTMLElement, JsmlTagProps<"nav">>,
 *     noscript: JsmlFactory<HTMLElement, JsmlTagProps<"noscript">>,
 *     object: JsmlFactory<HTMLObjectElement, JsmlTagProps<"object">>,
 *     ol: JsmlFactory<HTMLOListElement, JsmlTagProps<"ol">>,
 *     optgroup: JsmlFactory<HTMLOptGroupElement, JsmlTagProps<"optgroup">>,
 *     option: JsmlFactory<HTMLOptionElement, JsmlTagProps<"option">>,
 *     output: JsmlFactory<HTMLOutputElement, JsmlTagProps<"output">>,
 *     p: JsmlFactory<HTMLParagraphElement, JsmlTagProps<"p">>,
 *     picture: JsmlFactory<HTMLPictureElement, JsmlTagProps<"picture">>,
 *     pre: JsmlFactory<HTMLPreElement, JsmlTagProps<"pre">>,
 *     progress: JsmlFactory<HTMLProgressElement, JsmlTagProps<"progress">>,
 *     q: JsmlFactory<HTMLQuoteElement, JsmlTagProps<"q">>,
 *     rp: JsmlFactory<HTMLElement, JsmlTagProps<"rp">>,
 *     rt: JsmlFactory<HTMLElement, JsmlTagProps<"rt">>,
 *     ruby: JsmlFactory<HTMLElement, JsmlTagProps<"ruby">>,
 *     s: JsmlFactory<HTMLElement, JsmlTagProps<"s">>,
 *     samp: JsmlFactory<HTMLElement, JsmlTagProps<"samp">>,
 *     script: JsmlFactory<HTMLScriptElement, JsmlTagProps<"script">>,
 *     search: JsmlFactory<HTMLElement, JsmlTagProps<"search">>,
 *     section: JsmlFactory<HTMLElement, JsmlTagProps<"section">>,
 *     select: JsmlFactory<HTMLSelectElement, JsmlTagProps<"select">>,
 *     slot: JsmlFactory<HTMLSlotElement, JsmlTagProps<"slot">>,
 *     small: JsmlFactory<HTMLElement, JsmlTagProps<"small">>,
 *     source: JsmlFactory<HTMLSourceElement, JsmlTagProps<"source">>,
 *     span: JsmlFactory<HTMLSpanElement, JsmlTagProps<"span">>,
 *     strong: JsmlFactory<HTMLElement, JsmlTagProps<"strong">>,
 *     style: JsmlFactory<HTMLStyleElement, JsmlTagProps<"style">>,
 *     sub: JsmlFactory<HTMLElement, JsmlTagProps<"sub">>,
 *     summary: JsmlFactory<HTMLElement, JsmlTagProps<"summary">>,
 *     sup: JsmlFactory<HTMLElement, JsmlTagProps<"sup">>,
 *     table: JsmlFactory<HTMLTableElement, JsmlTagProps<"table">>,
 *     tbody: JsmlFactory<HTMLTableSectionElement, JsmlTagProps<"tbody">>,
 *     td: JsmlFactory<HTMLTableCellElement, JsmlTagProps<"td">>,
 *     template: JsmlFactory<HTMLTemplateElement, JsmlTagProps<"template">>,
 *     textarea: JsmlFactory<HTMLTextAreaElement, JsmlTagProps<"textarea">>,
 *     tfoot: JsmlFactory<HTMLTableSectionElement, JsmlTagProps<"tfoot">>,
 *     th: JsmlFactory<HTMLTableCellElement, JsmlTagProps<"th">>,
 *     thead: JsmlFactory<HTMLTableSectionElement, JsmlTagProps<"thead">>,
 *     time: JsmlFactory<HTMLTimeElement, JsmlTagProps<"time">>,
 *     title: JsmlFactory<HTMLTitleElement, JsmlTagProps<"title">>,
 *     tr: JsmlFactory<HTMLTableRowElement, JsmlTagProps<"tr">>,
 *     track: JsmlFactory<HTMLTrackElement, JsmlTagProps<"track">>,
 *     u: JsmlFactory<HTMLElement, JsmlTagProps<"u">>,
 *     ul: JsmlFactory<HTMLUListElement, JsmlTagProps<"ul">>,
 *     var: JsmlFactory<HTMLElement, JsmlTagProps<"var">>,
 *     video: JsmlFactory<HTMLVideoElement, JsmlTagProps<"video">>,
 *     wbr: JsmlFactory<HTMLElement, JsmlTagProps<"wbr">>,
 *     svg: JsmlFactory<SVGSVGElement, JsmlTagProps<"svg">>,
 *     g: JsmlFactory<SVGGElement, JsmlTagProps<"g">>,
 *     path: JsmlFactory<SVGPathElement, JsmlTagProps<"path">>,
 *     circle: JsmlFactory<SVGCircleElement, JsmlTagProps<"circle">>,
 *     ellipse: JsmlFactory<SVGEllipseElement, JsmlTagProps<"ellipse">>,
 *     line: JsmlFactory<SVGLineElement, JsmlTagProps<"line">>,
 *     polyline: JsmlFactory<SVGPolylineElement, JsmlTagProps<"polyline">>,
 *     polygon: JsmlFactory<SVGPolygonElement, JsmlTagProps<"polygon">>,
 *     rect: JsmlFactory<SVGRectElement, JsmlTagProps<"rect">>,
 *     use: JsmlFactory<SVGUseElement, JsmlTagProps<"use">>,
 *     defs: JsmlFactory<SVGDefsElement, JsmlTagProps<"defs">>,
 *     symbol: JsmlFactory<SVGSymbolElement, JsmlTagProps<"symbol">>,
 *     marker: JsmlFactory<SVGMarkerElement, JsmlTagProps<"marker">>,
 *     mask: JsmlFactory<SVGMaskElement, JsmlTagProps<"mask">>,
 *     pattern: JsmlFactory<SVGPatternElement, JsmlTagProps<"pattern">>,
 *     clipPath: JsmlFactory<SVGClipPathElement, JsmlTagProps<"clipPath">>,
 *     linearGradient: JsmlFactory<SVGLinearGradientElement, JsmlTagProps<"linearGradient">>,
 *     radialGradient: JsmlFactory<SVGRadialGradientElement, JsmlTagProps<"radialGradient">>,
 *     stop: JsmlFactory<SVGStopElement, JsmlTagProps<"stop">>,
 *     text: JsmlFactory<SVGTextElement, JsmlTagProps<"text">>,
 *     tspan: JsmlFactory<SVGTSpanElement, JsmlTagProps<"tspan">>,
 *     textPath: JsmlFactory<SVGTextPathElement, JsmlTagProps<"textPath">>,
 *     foreignObject: JsmlFactory<SVGForeignObjectElement, JsmlTagProps<"foreignObject">>,
 *     filter: JsmlFactory<SVGFilterElement, JsmlTagProps<"filter">>,
 *     feGaussianBlur: JsmlFactory<SVGFEGaussianBlurElement, JsmlTagProps<"feGaussianBlur">>,
 *     feOffset: JsmlFactory<SVGFEOffsetElement, JsmlTagProps<"feOffset">>,
 *     feBlend: JsmlFactory<SVGFEBlendElement, JsmlTagProps<"feBlend">>,
 *     feColorMatrix: JsmlFactory<SVGFEColorMatrixElement, JsmlTagProps<"feColorMatrix">>,
 *     feMerge: JsmlFactory<SVGFEMergeElement, JsmlTagProps<"feMerge">>,
 *     feMergeNode: JsmlFactory<SVGFEMergeNodeElement, JsmlTagProps<"feMergeNode">>,
 *     animate: JsmlFactory<SVGAnimateElement, JsmlTagProps<"animate">>,
 *     animateTransform: JsmlFactory<SVGAnimateTransformElement, JsmlTagProps<"animateTransform">>,
 * }} JsmlTags
 */

/**
 * @typedef {JsmlH
 *     & JsmlTags
 *     & { define: JsmlDefine, Fragment: typeof Fragment, Show: typeof Show, For: typeof For }
 *     & JsmlRegistered} Jsml
 */

// ---------------------------------------------------------------------------------------------------------------------
// Reactivity core
// ---------------------------------------------------------------------------------------------------------------------

/**
 * @typedef JsmlOwner
 * @property {JsmlOwner | null} parent
 * @property {Set<JsmlOwner> | null} owned
 * @property {(() => void)[] | null} cleanups
 * @property {Object<symbol, any> | null} context
 * @property {boolean} disposed
 * @property {(prev: any) => any} [fn]
 * @property {Set<JsmlSignalState<any>>} [deps]
 * @property {boolean} [isMemo]
 * @property {any} [value]
 */

/**
 * @template T
 * @typedef JsmlSignalState
 * @property {T} value
 * @property {Set<JsmlOwner>} observers
 * @property {((a: T, b: T) => boolean) | false} equals
 */

/** @type {JsmlOwner | null} */
let jsml_owner = null;
/** @type {JsmlOwner | null} */
let jsml_listener = null;
let jsml_batchDepth = 0;
let jsml_flushing = false;
/** @type {Set<JsmlOwner>} */
const jsml_memoQueue = new Set();
/** @type {Set<JsmlOwner>} */
const jsml_effectQueue = new Set();

/**
 * @param {JsmlOwner | null} parent
 * @returns {JsmlOwner}
 */
function jsml_createOwner(parent = jsml_owner) {
    const owner = { parent, owned: null, cleanups: null, context: null, disposed: false };

    if (parent !== null) {
        if (parent.owned === null) {
            parent.owned = new Set();
        }

        parent.owned.add(owner);
    }

    return owner;
}

/**
 * @param {JsmlOwner} owner
 */
function jsml_disposeChildren(owner) {
    if (owner.owned !== null) {
        for (const child of owner.owned) {
            jsml_dispose(child, false);
        }

        owner.owned.clear();
    }

    if (owner.cleanups !== null) {
        const cleanups = owner.cleanups;
        owner.cleanups = null;

        for (let i = cleanups.length - 1; i >= 0; i--) {
            try {
                cleanups[i]();
            } catch (error) {
                console.error(error);
            }
        }
    }
}

/**
 * @param {JsmlOwner} owner
 * @param {boolean} detach remove itself from parent's owned set
 */
function jsml_dispose(owner, detach = true) {
    if (owner.disposed) {
        return;
    }

    owner.disposed = true;
    jsml_disposeChildren(owner);

    if (owner.deps !== undefined) {
        jsml_unsubscribe(owner);
    }

    if (detach && owner.parent !== null && owner.parent.owned !== null) {
        owner.parent.owned.delete(owner);
    }
}

/**
 * @param {JsmlOwner} computation
 */
function jsml_unsubscribe(computation) {
    for (const signal of computation.deps) {
        signal.observers.delete(computation);
    }

    computation.deps.clear();
}

/**
 * @template T
 * @param {T} value
 * @param {{ equals?: ((a: T, b: T) => boolean) | false }} options
 * @returns {JsmlSignalState<T>}
 */
function jsml_signalState(value, options = {}) {
    return {
        value,
        observers: new Set(),
        equals: options.equals === undefined ? Object.is : options.equals,
    };
}

/**
 * @template T
 * @param {JsmlSignalState<T>} signal
 * @returns {T}
 */
function jsml_read(signal) {
    if (jsml_listener !== null && !jsml_listener.disposed) {
        signal.observers.add(jsml_listener);
        jsml_listener.deps.add(signal);
    }

    return signal.value;
}

/**
 * @template T
 * @param {JsmlSignalState<T>} signal
 * @param {T} value
 */
function jsml_write(signal, value) {
    if (signal.equals !== false && signal.equals(signal.value, value)) {
        return;
    }

    signal.value = value;

    for (const observer of signal.observers) {
        (observer.isMemo ? jsml_memoQueue : jsml_effectQueue).add(observer);
    }

    if (jsml_batchDepth === 0 && !jsml_flushing) {
        jsml_flush();
    }
}

/**
 * @param {JsmlOwner} computation
 */
function jsml_run(computation) {
    if (computation.disposed) {
        return;
    }

    jsml_unsubscribe(computation);
    jsml_disposeChildren(computation);

    const previousOwner = jsml_owner;
    const previousListener = jsml_listener;
    jsml_owner = computation;
    jsml_listener = computation;

    try {
        computation.value = computation.fn(computation.value);
    } catch (error) {
        console.error(error);
    } finally {
        jsml_owner = previousOwner;
        jsml_listener = previousListener;
    }
}

function jsml_flush() {
    if (jsml_flushing) {
        return;
    }

    jsml_flushing = true;
    let guard = 0;

    try {
        // memos first, so effects never observe a stale derived value
        while (jsml_memoQueue.size !== 0 || jsml_effectQueue.size !== 0) {
            if (++guard > 100_000) {
                jsml_memoQueue.clear();
                jsml_effectQueue.clear();
                throw new Error("jsml: possible infinite update loop (a computation keeps writing to its own dependency)");
            }

            const queue = jsml_memoQueue.size !== 0 ? jsml_memoQueue : jsml_effectQueue;
            const computation = queue.values().next().value;
            queue.delete(computation);
            jsml_run(computation);
        }
    } finally {
        jsml_flushing = false;
    }
}

/**
 * @param {(prev: any) => any} fn
 * @param {boolean} isMemo
 * @returns {JsmlOwner}
 */
function jsml_computation(fn, isMemo) {
    const computation = jsml_createOwner();
    computation.fn = fn;
    computation.deps = new Set();
    computation.isMemo = isMemo;
    computation.value = undefined;
    return computation;
}

/**
 * Creates a reactive value. `set` accepts a value or an updater `prev => next`.
 * To store a function in a signal use `set(() => fn)`.
 *
 * @example const [count, setCount] = signal(0); setCount(c => c + 1);
 *
 * @template T
 * @param {T} [initial]
 * @param {SignalOptions<T>} [options] `equals: false` notifies on every set
 * @returns {Signal<T>}
 */
function signal(initial, options) {
    const state = jsml_signalState(initial, options);

    return [
        () => jsml_read(state),
        (value) => {
            const next = typeof value === "function"
                ? value(state.value)
                : value;

            jsml_write(state, next);
            return next;
        }
    ];
}

/**
 * Runs `fn` now and again whenever a signal it read changes. Returns a disposer.
 *
 * @template T
 * @param {(prev: T | undefined) => T} fn receives its previous return value
 * @returns {() => void}
 */
function effect(fn) {
    const computation = jsml_computation(fn, false);
    jsml_run(computation);
    return () => jsml_dispose(computation);
}

/**
 * Derived, cached value. Downstream only updates when the result changes (per `equals`).
 *
 * @template T
 * @param {(prev: T | undefined) => T} fn
 * @param {T} [initial]
 * @param {SignalOptions<T>} [options]
 * @returns {Accessor<T>}
 */
function memo(fn, initial, options) {
    const state = jsml_signalState(initial, options);
    const computation = jsml_computation(() => jsml_write(state, fn(state.value)), true);
    jsml_run(computation);
    return () => jsml_read(state);
}

/**
 * Groups writes, dependants run once at the end.
 *
 * @template T
 * @param {() => T} fn
 * @returns {T}
 */
function batch(fn) {
    jsml_batchDepth++;

    try {
        return fn();
    } finally {
        jsml_batchDepth--;

        if (jsml_batchDepth === 0) {
            jsml_flush();
        }
    }
}

/**
 * Reads signals inside `fn` without subscribing to them.
 *
 * @template T
 * @param {() => T} fn
 * @returns {T}
 */
function untrack(fn) {
    const previousListener = jsml_listener;
    jsml_listener = null;

    try {
        return fn();
    } finally {
        jsml_listener = previousListener;
    }
}

/**
 * @template T
 * @param {JsmlOwner | null} owner
 * @param {() => T} fn
 * @returns {T}
 */
function runWithOwner(owner, fn) {
    const previousOwner = jsml_owner;
    const previousListener = jsml_listener;
    jsml_owner = owner;
    jsml_listener = null;

    try {
        return fn();
    } finally {
        jsml_owner = previousOwner;
        jsml_listener = previousListener;
    }
}

/**
 * @returns {JsmlOwner | null}
 */
function getOwner() {
    return jsml_owner;
}

/**
 * Creates a scope that is not disposed by its parent. Context is still inherited.
 *
 * @template T
 * @param {(dispose: () => void) => T} fn
 * @returns {T}
 */
function createRoot(fn) {
    const root = jsml_createOwner(null);
    root.parent = jsml_owner; // for context lookup only, not registered in parent.owned
    return runWithOwner(root, () => fn(() => jsml_dispose(root)));
}

/**
 * Registers `fn` to run when the current scope is disposed or re-run.
 *
 * @param {() => void} fn
 * @returns {() => void}
 */
function onCleanup(fn) {
    if (jsml_owner !== null) {
        if (jsml_owner.cleanups === null) {
            jsml_owner.cleanups = [];
        }

        jsml_owner.cleanups.push(fn);
    }

    return fn;
}

/** @type {{ fn: () => void, owner: JsmlOwner | null }[]} */
const jsml_mounts = [];
let jsml_mountScheduled = false;

function jsml_flushMounts() {
    jsml_mountScheduled = false;

    for (const { fn, owner } of jsml_mounts.splice(0)) {
        if (owner !== null && owner.disposed) {
            continue;
        }

        try {
            runWithOwner(owner, fn);
        } catch (error) {
            console.error(error);
        }
    }
}

/**
 * Runs `fn` (untracked) after the current render. `render()` flushes it synchronously once nodes are in the document,
 * otherwise it runs in a microtask. `onCleanup` inside `fn` is tied to the component's scope.
 *
 * @param {() => void} fn
 */
function onMount(fn) {
    jsml_mounts.push({ fn, owner: jsml_owner });

    if (!jsml_mountScheduled) {
        jsml_mountScheduled = true;
        queueMicrotask(jsml_flushMounts);
    }
}

// ---------------------------------------------------------------------------------------------------------------------
// Impulse interop & helpers
// ---------------------------------------------------------------------------------------------------------------------

/**
 * Duck-typed so this file does not depend on Impulse.js being loaded.
 *
 * @param {any} value
 * @returns {boolean}
 */
function jsml_isImpulse(value) {
    return value !== null
        && typeof value === "object"
        && typeof value.listen === "function"
        && typeof value.value === "function";
}

/**
 * Converts an Impulse to an accessor. The listener is removed when the current scope is disposed.
 *
 * @template T
 * @param {Impulse<T>} impulse
 * @returns {Accessor<T>}
 */
function fromImpulse(impulse) {
    const state = jsml_signalState(impulse.value(), { equals: false });
    const listener = (value) => jsml_write(state, value);

    impulse.listen(listener);
    onCleanup(() => impulse.removeListener?.(listener));

    return () => jsml_read(state);
}

/**
 * Unwraps a possibly reactive value: accessor → its value, Impulse → its value, anything else as is.
 * Use it in components for props that may or may not be reactive: `read(props.count)`.
 *
 * @template T
 * @param {MaybeReactive<T>} value
 * @returns {T}
 */
function read(value) {
    if (typeof value === "function") {
        return value();
    }

    if (jsml_isImpulse(value)) {
        return jsml_listener !== null
            ? fromImpulse(value)()
            : value.value();
    }

    return value;
}

/**
 * @param {any} value
 * @returns {boolean}
 */
function jsml_isReactive(value) {
    return typeof value === "function" || jsml_isImpulse(value);
}

// ---------------------------------------------------------------------------------------------------------------------
// DOM insertion
// ---------------------------------------------------------------------------------------------------------------------

/** @type {WeakSet<Text>} text nodes created by jsml for primitive values, safe to mutate in place */
const jsml_ownText = new WeakSet();

/**
 * @param {any} value
 * @returns {Text}
 */
function jsml_text(value) {
    const text = document.createTextNode(String(value));
    jsml_ownText.add(text);
    return text;
}

/**
 * @param {any} value
 * @returns {boolean}
 */
function jsml_isList(value) {
    return Array.isArray(value)
        || value instanceof NodeList
        || value instanceof HTMLCollection;
}

/**
 * Flattens any content into a list of nodes. Functions are called (and tracked by the running computation).
 *
 * @param {any} value
 * @param {Node[]} out
 * @returns {Node[]}
 */
function jsml_resolve(value, out = []) {
    if (value === undefined || value === null || typeof value === "boolean") {
        return out;
    }

    if (typeof value === "function") {
        return jsml_resolve(value(), out);
    }

    if (jsml_isImpulse(value)) {
        return jsml_resolve(read(value), out);
    }

    if (value instanceof DocumentFragment) {
        out.push(...value.childNodes);
        return out;
    }

    if (value instanceof Node) {
        out.push(value);
        return out;
    }

    if (jsml_isList(value)) {
        for (const item of Array.from(value)) {
            jsml_resolve(item, out);
        }

        return out;
    }

    out.push(jsml_text(value));
    return out;
}

/**
 * Makes `parent`'s children between the previous content and `end` equal to `next`, moving only what is out of place.
 *
 * @param {Node} parent
 * @param {Node[]} current
 * @param {Node[]} next
 * @param {Node} end
 */
function jsml_reconcile(parent, current, next, end) {
    const keep = new Set(next);

    for (const node of current) {
        if (!keep.has(node) && node.parentNode === parent) {
            parent.removeChild(node);
        }
    }

    let reference = end;
    for (let i = next.length - 1; i >= 0; i--) {
        const node = next[i];

        if (node.parentNode !== parent || node.nextSibling !== reference) {
            parent.insertBefore(node, reference);
        }

        reference = node;
    }
}

/**
 * Inserts a dynamic region: an effect keeps the nodes before `marker` in sync with `accessor()`.
 *
 * @param {Node} parent
 * @param {() => any} accessor
 * @param {Node | null} marker
 */
function jsml_region(parent, accessor, marker) {
    const end = document.createComment("");
    parent.insertBefore(end, marker);

    /** @type {Node[]} */
    let current = [];

    effect(() => {
        const value = accessor();
        const host = end.parentNode;

        if (host === null) {
            return;
        }

        // fast path: primitive replacing a primitive just updates the text
        if (
            (typeof value === "string" || typeof value === "number" || typeof value === "bigint")
            && current.length === 1
            && jsml_ownText.has(current[0])
        ) {
            current[0].data = String(value);
            return;
        }

        const next = jsml_resolve(value);
        jsml_reconcile(host, current, next, end);
        current = next;
    });
}

/**
 * Appends any content to `parent` before `marker`. Functions and Impulses become dynamic regions.
 *
 * @param {Node} parent
 * @param {any} value
 * @param {Node | null} marker
 */
function jsml_append(parent, value, marker = null) {
    if (value === undefined || value === null || typeof value === "boolean") {
        return;
    }

    if (typeof value === "function") {
        jsml_region(parent, value, marker);
        return;
    }

    if (jsml_isImpulse(value)) {
        jsml_region(parent, fromImpulse(value), marker);
        return;
    }

    if (value instanceof Node) {
        parent.insertBefore(value, marker);
        return;
    }

    if (jsml_isList(value)) {
        for (const item of Array.from(value)) {
            jsml_append(parent, item, marker);
        }

        return;
    }

    parent.insertBefore(jsml_text(value), marker);
}

// ---------------------------------------------------------------------------------------------------------------------
// Elements & props
// ---------------------------------------------------------------------------------------------------------------------

const JSML_SVG_NS = "http://www.w3.org/2000/svg";
const JSML_XLINK_NS = "http://www.w3.org/1999/xlink";

/** Tags created in the SVG namespace by the proxy API (inside `html` templates the namespace is tracked properly). */
const JSML_SVG_TAGS = new Set([
    "svg", "g", "path", "circle", "ellipse", "line", "polyline", "polygon", "rect", "use", "defs", "symbol",
    "marker", "mask", "pattern", "clipPath", "linearGradient", "radialGradient", "stop", "text", "tspan", "textPath",
    "foreignObject", "filter", "feGaussianBlur", "feOffset", "feBlend", "feColorMatrix", "feMerge", "feMergeNode",
    "animate", "animateTransform",
]);

/** SVG attributes that are camelCase and must not be converted to kebab-case. */
const JSML_SVG_CAMEL_ATTRIBUTES = new Set([
    "viewBox", "preserveAspectRatio", "gradientUnits", "gradientTransform", "patternUnits", "patternContentUnits",
    "patternTransform", "clipPathUnits", "maskUnits", "maskContentUnits", "markerWidth", "markerHeight",
    "markerUnits", "refX", "refY", "pathLength", "textLength", "lengthAdjust", "startOffset", "stdDeviation",
    "baseFrequency", "numOctaves", "filterUnits", "primitiveUnits", "spreadMethod", "attributeName", "repeatCount",
    "keyTimes", "keySplines", "calcMode", "tableValues",
]);

/** Keys that are also written as DOM properties, so updates work after the user interacted with the element. */
const JSML_PROPERTIES = new Set(["value", "checked", "selected", "muted", "indeterminate"]);

/**
 * @param {string} string
 * @returns {string}
 */
function jsml_camelToKebab(string) {
    return string.replace(/[A-Z]/g, (char) => "-" + char.toLowerCase());
}

/**
 * @param {Element} element
 * @param {string} name
 * @param {any} value
 */
function jsml_setAttribute(element, name, value) {
    if (JSML_PROPERTIES.has(name) && name in element) {
        element[name] = name === "value"
            ? (value ?? "")
            : Boolean(value);
    }

    if (value === undefined || value === null || value === false) {
        element.removeAttribute(name);
        return;
    }

    if (name.startsWith("xlink:")) {
        element.setAttributeNS(JSML_XLINK_NS, name, value === true ? "" : String(value));
        return;
    }

    element.setAttribute(name, value === true ? "" : String(value));
}

/**
 * @param {any} value string | string[] | { [className]: boolean | accessor }
 * @returns {string}
 */
function jsml_classString(value) {
    value = read(value);

    if (value === undefined || value === null || value === false) {
        return "";
    }

    if (Array.isArray(value)) {
        return value
            .map(jsml_classString)
            .filter(Boolean)
            .join(" ");
    }

    if (typeof value === "object") {
        return Object.keys(value)
            .filter(key => read(value[key]))
            .join(" ");
    }

    return String(value);
}

/**
 * @param {HTMLElement | SVGElement} element
 * @param {string} key
 * @param {any} value
 */
function jsml_setStyleProperty(element, key, value) {
    const name = key.startsWith("--")
        ? key
        : jsml_camelToKebab(key);

    if (value === undefined || value === null || value === false) {
        element.style.removeProperty(name);
        return;
    }

    let string = String(value);
    let priority = "";

    if (/!important\s*$/.test(string)) {
        priority = "important";
        string = string.replace(/\s*!important\s*$/, "");
    }

    element.style.setProperty(name, string, priority);
}

/**
 * @param {HTMLElement | SVGElement} element
 * @param {any} style string | object (values may be accessors) | accessor returning either
 */
function jsml_applyStyle(element, style) {
    if (typeof style === "string") {
        element.setAttribute("style", style);
        return;
    }

    if (jsml_isReactive(style)) {
        /** @type {string[]} */
        let previous = [];

        effect(() => {
            const value = read(style);

            if (typeof value === "string" || value === undefined || value === null) {
                previous = [];
                jsml_setAttribute(element, "style", value);
                return;
            }

            for (const key of previous) {
                if (!(key in value)) {
                    jsml_setStyleProperty(element, key, undefined);
                }
            }

            for (const key in value) {
                jsml_setStyleProperty(element, key, read(value[key]));
            }

            previous = Object.keys(value);
        });

        return;
    }

    for (const key in style) {
        const value = style[key];

        if (jsml_isReactive(value)) {
            effect(() => jsml_setStyleProperty(element, key, read(value)));
            continue;
        }

        jsml_setStyleProperty(element, key, value);
    }
}

/**
 * Applies props to an element.
 *
 *  - `class` / `className`: string | array | object map; values may be accessors
 *  - `style`: string | object | accessor
 *  - `onClick`: listener for "click" (name lower-cased, same as current jsml); `on:myEvent` keeps the exact name
 *  - `prop:name`: sets a DOM property, `attr:name`: sets an attribute without name conversion
 *  - `ref`: called with the element
 *  - `\key`: escapes the key (e.g. `\onclick` sets the attribute)
 *  - anything else: attribute; functions and Impulses are reactive
 *
 * @param {HTMLElement | SVGElement} element
 * @param {Object<string, any>} props
 * @param {boolean} convertNames camelCase → kebab-case attribute names (proxy API); templates use names as written
 */
function jsml_spread(element, props, convertNames) {
    const isSvg = element.namespaceURI === JSML_SVG_NS;
    let ref;

    for (const key in props) {
        const value = props[key];

        if (key === "children") {
            continue;
        }

        if (key === "ref") {
            ref = value;
            continue;
        }

        if (key === "class" || key === "className") {
            if (typeof value === "string") {
                element.setAttribute("class", value);
                continue;
            }

            effect(() => jsml_setAttribute(element, "class", jsml_classString(value) || undefined));
            continue;
        }

        if (key === "style") {
            jsml_applyStyle(element, value);
            continue;
        }

        if (key.startsWith("on:")) {
            element.addEventListener(key.substring(3), value);
            continue;
        }

        if (key[0] === "o" && key[1] === "n") {
            if (typeof value === "function") {
                element.addEventListener(key.substring(2).toLowerCase(), value);
                continue;
            }

            if (value === undefined || value === null) {
                continue;
            }

            // a string here would become an inline handler (code execution), the old jsml threw as well
            throw new TypeError(`jsml: "${key}" must be a function, got ${typeof value}. Use "\\${key}" to set the attribute on purpose`);
        }

        if (key.startsWith("prop:")) {
            const name = key.substring(5);

            if (jsml_isReactive(value)) {
                effect(() => element[name] = read(value));
                continue;
            }

            element[name] = value;
            continue;
        }

        let name;
        if (key.startsWith("attr:")) {
            name = key.substring(5);
        } else {
            name = key[0] === "\\"
                ? key.substring(1)
                : key;

            if (convertNames && !(isSvg && JSML_SVG_CAMEL_ATTRIBUTES.has(name))) {
                name = jsml_camelToKebab(name);
            }
        }

        if (jsml_isReactive(value)) {
            effect(() => jsml_setAttribute(element, name, read(value)));
            continue;
        }

        jsml_setAttribute(element, name, value);
    }

    if (typeof ref === "function") {
        ref(element);
    } else if (ref !== undefined && ref !== null && typeof ref === "object") {
        ref.current = element;
    }
}

/**
 * @param {string} tag
 * @param {boolean} isSvg
 * @returns {HTMLElement | SVGElement}
 */
function jsml_createElement(tag, isSvg) {
    return isSvg
        ? document.createElementNS(JSML_SVG_NS, tag)
        : document.createElement(tag);
}

/**
 * @param {any} value
 * @returns {boolean}
 */
function jsml_isContentLike(value) {
    return value instanceof Node || jsml_isList(value);
}

/**
 * Proxy API element: `jsml.div(props, ...content)`.
 *
 * @param {string} tag
 * @param {JsmlProps | JsmlContent} props
 * @param {JsmlContent[]} content
 * @returns {HTMLElement | SVGElement}
 */
function jsml_element(tag, props, content) {
    if (jsml_isContentLike(props)) {
        content.unshift(props);
        props = undefined;
    }

    if (typeof props === "string") {
        props = { class: props };
    }

    const element = jsml_createElement(tag, JSML_SVG_TAGS.has(tag));

    if (content.length === 0 && props !== undefined && props !== null && "children" in props) {
        jsml_append(element, props.children);
    } else {
        jsml_append(element, content.length === 1 ? content[0] : content);
    }

    if (props !== undefined && props !== null) {
        jsml_spread(element, props, true);
    }

    return element;
}

/**
 * Kept for code that calls the original internals directly (Components.js → Svg, Editor.js, inspector.js).
 *
 * @param {Element} element
 * @param {JsmlProps} props
 */
function jsml_addProps(element, props) {
    if (props === undefined || props === null) {
        return;
    }

    jsml_spread(element, typeof props === "string" ? { class: props } : props, true);
}

/**
 * @param {Element} element
 * @param {JsmlContent} content
 */
function jsml_addContent(element, content) {
    jsml_append(element, content);
}

// ---------------------------------------------------------------------------------------------------------------------
// Components
// ---------------------------------------------------------------------------------------------------------------------

/**
 * Components are plain functions `(props) => content`. They run once, untracked.
 * `props.children` holds the children (single child → itself, more → array).
 *
 * @template P
 * @param {(props: P) => any} component
 * @param {P} props
 * @returns {any}
 */
function jsml_component(component, props) {
    return untrack(() => component(props));
}

/**
 * @param {any} props
 * @param {any[]} children
 * @returns {Object<string, any>}
 */
function jsml_componentProps(props, children) {
    if (jsml_isContentLike(props)) {
        children.unshift(props);
        props = undefined;
    }

    const result = props === undefined || props === null
        ? {}
        : typeof props === "string"
            ? { class: props }
            : { ...props };

    if (children.length === 1) {
        result.children = children[0];
    } else if (children.length > 1) {
        result.children = children;
    }

    return result;
}

/**
 * React.createElement-like entry point: `jsml(type, props, ...children)`.
 *
 * @param {string | Function} type
 * @param {any} props
 * @param {...any} children
 * @returns {any}
 */
function jsml_h(type, props, ...children) {
    if (typeof type === "string") {
        return jsml_element(type, props, children);
    }

    if (typeof type === "function") {
        return jsml_component(type, jsml_componentProps(props, children));
    }

    throw new TypeError(`jsml: expected a tag name or a component function, got ${String(type)}`);
}

/** @type {Map<string, Function>} */
const jsml_registry = new Map();

/**
 * Registers components so they are reachable as `jsml.Name(props, ...children)`.
 * `jsml.define({ Card, Modal })` or `jsml.define("Card", Card)`.
 *
 * Returns callers that keep the components' prop types: `const { Card } = jsml.define({ Card })`.
 *
 * @type {JsmlDefine}
 */
function jsml_define(nameOrMap, component) {
    const entries = typeof nameOrMap === "string"
        ? [[nameOrMap, component]]
        : Object.entries(nameOrMap);

    for (const [name, fn] of entries) {
        if (typeof fn !== "function") {
            throw new TypeError(`jsml.define: "${name}" is not a function`);
        }

        jsml_registry.set(name, fn);
    }

    const callers = {};
    for (const [name, fn] of entries) {
        callers[name] = (props, ...content) => jsml_component(fn, jsml_componentProps(props, content));
    }

    return typeof nameOrMap === "string"
        ? callers[nameOrMap]
        : callers;
}

/**
 * Resolves children inside the current scope and memoizes the resulting nodes (like Solid's `children()`).
 * Useful when a component needs to inspect or reuse its children.
 *
 * @param {() => JsmlContent} fn usually `() => props.children`
 * @returns {Accessor<Node[]>}
 */
function children(fn) {
    const raw = memo(() => {
        const value = fn();
        return typeof value === "function" && value.length === 0
            ? value()
            : value;
    });

    return memo(() => jsml_resolve(raw()), undefined, { equals: false });
}

/**
 * @param {{ children?: JsmlContent }} props
 * @returns {JsmlContent}
 */
function Fragment(props) {
    return props.children;
}

/**
 * Renders children while `when` is truthy, `fallback` otherwise. Children are (re)created only when the
 * truthiness flips. A children function with a parameter receives an accessor of the `when` value.
 *
 * @example Show({ when: user, fallback: "Loading…", children: (user) => user().name })
 *
 * @template T
 * @param {{ when: MaybeReactive<T>, fallback?: JsmlContent, children?: JsmlContent | ((value: Accessor<T>) => JsmlContent) }} props
 * @returns {Accessor<JsmlContent>}
 */
function Show(props) {
    const condition = memo(() => read(props.when), undefined, { equals: (a, b) => !a === !b });

    return memo(() => {
        if (condition()) {
            return untrack(() => {
                const content = props.children;
                return typeof content === "function" && content.length > 0
                    ? content(() => read(props.when))
                    : content;
            });
        }

        return untrack(() => props.fallback);
    });
}

/**
 * Keyed list (by item identity). Rows are created once per item and moved, not recreated, when the list changes.
 * `children` is `(item, index) => content`, where `index` is an accessor.
 *
 * @template T
 * @example For({ each: todos, children: (todo, index) => jsml.li(_, () => index() + 1, ". ", todo.text) })
 *
 * @template T
 * @param {{ each: MaybeReactive<Iterable<T> | ArrayLike<T> | null | undefined>, fallback?: JsmlContent, children: ((item: T, index: Accessor<number>) => JsmlContent) | [(item: T, index: Accessor<number>) => JsmlContent] }} props
 * @returns {Accessor<JsmlContent>}
 */
function For(props) {
    let render = props.children;
    if (Array.isArray(render) && render.length === 1) {
        render = render[0];
    }

    if (typeof render !== "function") {
        throw new TypeError("jsml: <For> expects a single function child: (item, index) => content");
    }

    const owner = jsml_createOwner();

    /**
     * @typedef ForRow
     * @property {() => Node[]} nodes
     * @property {(index: number) => void} setIndex
     * @property {JsmlOwner} owner
     */

    /** @type {Map<T, ForRow[]>} */
    let rows = new Map();

    return memo(() => {
        const list = read(props.each);
        const items = list === undefined || list === null
            ? []
            : Array.from(list);

        /** @type {ForRow[]} */
        const ordered = untrack(() => {
            /** @type {Map<T, ForRow[]>} */
            const next = new Map();
            const result = [];

            for (let i = 0; i < items.length; i++) {
                const item = items[i];
                const bucket = rows.get(item);
                let row;

                if (bucket !== undefined && bucket.length !== 0) {
                    row = bucket.shift();
                    row.setIndex(i);
                } else {
                    const rowOwner = jsml_createOwner(owner);
                    row = runWithOwner(rowOwner, () => {
                        const [index, setIndex] = signal(i);
                        const value = render(item, index);
                        return { nodes: memo(() => jsml_resolve(value), undefined, { equals: false }), setIndex, owner: rowOwner };
                    });
                }

                if (!next.has(item)) {
                    next.set(item, []);
                }

                next.get(item).push(row);
                result.push(row);
            }

            for (const bucket of rows.values()) {
                for (const row of bucket) {
                    jsml_dispose(row.owner);
                }
            }

            rows = next;
            return result;
        });

        if (ordered.length === 0) {
            return untrack(() => props.fallback);
        }

        const nodes = [];
        for (const row of ordered) {
            nodes.push(...row.nodes());
        }

        return nodes;
    }, undefined, { equals: false });
}

/**
 * @template T
 * @typedef JsmlContext
 * @property {symbol} id
 * @property {T} defaultValue
 * @property {(props: { value: T, children?: JsmlContent }) => JsmlContent} Provider
 */

/**
 * @template T
 * @param {T} [defaultValue]
 * @returns {JsmlContext<T>}
 */
function createContext(defaultValue) {
    const id = Symbol("jsml.context");

    /**
     * @param {{ value: T, children?: JsmlContent }} props
     * @returns {JsmlContent}
     */
    function Provider(props) {
        const owner = jsml_createOwner();
        owner.context = { [id]: props.value };
        return runWithOwner(owner, () => children(() => props.children));
    }

    return { id, defaultValue, Provider };
}

/**
 * Children must be created inside the Provider for this to work: automatic in `html` templates,
 * with the function API pass them lazily: `jsml(Ctx.Provider, { value }, () => jsml(Child))`.
 *
 * @template T
 * @param {JsmlContext<T>} context
 * @returns {T}
 */
function useContext(context) {
    let owner = jsml_owner;

    while (owner !== null) {
        if (owner.context !== null && context.id in owner.context) {
            return owner.context[context.id];
        }

        owner = owner.parent;
    }

    return context.defaultValue;
}

/**
 * Mounts `code` into `container`. Returns a disposer that removes the nodes and runs all cleanups.
 *
 * @param {(() => JsmlContent) | JsmlContent} code e.g. `() => html\`<${App} />\``
 * @param {Node} container
 * @returns {() => void}
 */
function render(code, container) {
    const start = document.createComment("jsml");
    const end = document.createComment("/jsml");
    container.appendChild(start);
    container.appendChild(end);

    const dispose = createRoot((dispose) => {
        const value = untrack(() => typeof code === "function" ? code() : code);
        jsml_append(container, value, end);
        return dispose;
    });

    jsml_flushMounts();

    return () => {
        dispose();

        if (start.parentNode === null) {
            return;
        }

        const parent = start.parentNode;
        let node = start;
        while (node !== null) {
            const next = node.nextSibling;
            parent.removeChild(node);

            if (node === end) {
                break;
            }

            node = next;
        }
    };
}

// ---------------------------------------------------------------------------------------------------------------------
// html`` tagged templates
// ---------------------------------------------------------------------------------------------------------------------

/**
 * @typedef {{ type: "text", value: string }} JsmlTextNode
 * @typedef {{ type: "slot", index: number }} JsmlSlotNode
 * @typedef {{ type: "fragment", children: JsmlTemplateNode[] }} JsmlFragmentNode
 * @typedef {{ kind: "attribute", name: string, value: true | (string | JsmlSlotNode)[] } | { kind: "spread", index: number }} JsmlTemplateProp
 * @typedef {{ type: "element", tag: string | JsmlSlotNode, props: JsmlTemplateProp[], children: JsmlTemplateNode[], closeIndex?: number }} JsmlElementNode
 * @typedef {JsmlTextNode | JsmlSlotNode | JsmlFragmentNode | JsmlElementNode} JsmlTemplateNode
 */

const JSML_VOID_TAGS = new Set([
    "area", "base", "br", "col", "embed", "hr", "img", "input", "link", "meta", "source", "track", "wbr",
]);

const JSML_SLOT_OPEN = "";
const JSML_SLOT_CLOSE = "";
const JSML_SLOT_SPLIT = /(\d+)/;

/** @type {WeakMap<TemplateStringsArray, JsmlTemplateNode[]>} */
const jsml_templateCache = new WeakMap();

/** @type {HTMLTextAreaElement | undefined} */
let jsml_decoder;

/**
 * @param {string} string
 * @returns {string}
 */
function jsml_decodeEntities(string) {
    if (!string.includes("&")) {
        return string;
    }

    jsml_decoder ??= document.createElement("textarea");
    jsml_decoder.innerHTML = string;
    return jsml_decoder.value;
}

/**
 * JSX whitespace rules: text with line breaks is trimmed per line, blank lines dropped, lines joined by a space.
 *
 * @param {string} string
 * @returns {string}
 */
function jsml_jsxWhitespace(string) {
    if (!/[\r\n]/.test(string)) {
        return string;
    }

    const lines = string.split(/\r\n|\n|\r/);
    let result = "";

    for (let i = 0; i < lines.length; i++) {
        let line = lines[i];

        if (i !== 0) {
            line = line.replace(/^\s+/, "");
        }

        if (i !== lines.length - 1) {
            line = line.replace(/\s+$/, "");
        }

        if (line === "") {
            continue;
        }

        result += result === ""
            ? line
            : " " + line;
    }

    return result;
}

/**
 * @param {string} string may contain slot markers
 * @returns {(string | JsmlSlotNode)[]}
 */
function jsml_splitSlots(string) {
    const parts = string.split(JSML_SLOT_SPLIT);
    const result = [];

    for (let i = 0; i < parts.length; i++) {
        if (i % 2 === 1) {
            result.push({ type: "slot", index: Number(parts[i]) });
            continue;
        }

        if (parts[i] !== "") {
            result.push(parts[i]);
        }
    }

    return result;
}

/**
 * @param {TemplateStringsArray} strings
 * @returns {JsmlTemplateNode[]}
 */
function jsml_parseTemplate(strings) {
    let source = "";
    for (let i = 0; i < strings.length; i++) {
        if (strings[i].includes(JSML_SLOT_OPEN) || strings[i].includes(JSML_SLOT_CLOSE)) {
            throw new SyntaxError("html``: template contains reserved characters U+E000 / U+E001");
        }

        source += strings[i];

        if (i < strings.length - 1) {
            source += JSML_SLOT_OPEN + i + JSML_SLOT_CLOSE;
        }
    }

    let position = 0;

    const fail = (message) => {
        const context = source
            .substring(Math.max(0, position - 30), position + 30)
            .replace(/(\d+)/g, "${…}");

        throw new SyntaxError(`html\`\`: ${message} near: ${JSON.stringify(context)}`);
    };

    const skipWhitespace = () => {
        while (position < source.length && /\s/.test(source[position])) {
            position++;
        }
    };

    /** @returns {number | undefined} slot index if a slot starts at the current position */
    const readSlot = () => {
        if (source[position] !== JSML_SLOT_OPEN) {
            return undefined;
        }

        const end = source.indexOf(JSML_SLOT_CLOSE, position);
        const index = Number(source.substring(position + 1, end));
        position = end + 1;
        return index;
    };

    /**
     * @param {RegExp} stop
     * @returns {string}
     */
    const readUntil = (stop) => {
        const start = position;

        while (position < source.length && !stop.test(source[position]) && !source.startsWith("/>", position)) {
            position++;
        }

        return source.substring(start, position);
    };

    /**
     * @param {string} raw
     * @param {JsmlTemplateNode[]} out
     */
    const pushText = (raw, out) => {
        for (const part of jsml_splitSlots(raw)) {
            if (typeof part !== "string") {
                out.push(part);
                continue;
            }

            const text = jsml_jsxWhitespace(part);
            if (text !== "") {
                out.push({ type: "text", value: jsml_decodeEntities(text) });
            }
        }
    };

    /**
     * Is `<` at the current position the start of a tag (otherwise it is literal text, e.g. `a < b`)?
     * @returns {boolean}
     */
    const isTagStart = () => {
        const next = source[position + 1];
        return next !== undefined && /[A-Za-z/>!]/.test(next);
    };

    /**
     * @param {JsmlTemplateProp[]} props filled in place
     * @returns {boolean} whether the tag was self-closing
     */
    const parseProps = (props) => {
        while (true) {
            skipWhitespace();

            if (position >= source.length) {
                fail("unterminated tag");
            }

            if (source.startsWith("/>", position)) {
                position += 2;
                return true;
            }

            if (source[position] === ">") {
                position++;
                return false;
            }

            if (source.startsWith("...", position)) {
                position += 3;
                const index = readSlot();

                if (index === undefined) {
                    fail("expected ${…} after ...");
                }

                props.push({ kind: "spread", index });
                continue;
            }

            if (source[position] === JSML_SLOT_OPEN) {
                fail("an expression can not be an attribute name, use ...${object} to spread props");
            }

            const name = readUntil(/[\s=>]/);
            if (name === "") {
                fail("expected attribute name");
            }

            skipWhitespace();

            if (source[position] !== "=") {
                props.push({ kind: "attribute", name, value: true });
                continue;
            }

            position++;
            skipWhitespace();

            const quote = source[position];
            if (quote === "\"" || quote === "'") {
                const end = source.indexOf(quote, position + 1);

                if (end === -1) {
                    fail(`unterminated attribute value for "${name}"`);
                }

                const raw = source.substring(position + 1, end);
                position = end + 1;

                props.push({
                    kind: "attribute",
                    name,
                    value: jsml_splitSlots(raw).map(part => typeof part === "string" ? jsml_decodeEntities(part) : part),
                });

                continue;
            }

            const index = readSlot();
            if (index !== undefined) {
                props.push({ kind: "attribute", name, value: [{ type: "slot", index }] });
                continue;
            }

            const raw = readUntil(/[\s>]/);
            if (raw === "") {
                fail(`expected value for "${name}"`);
            }

            props.push({ kind: "attribute", name, value: jsml_splitSlots(raw) });
        }
    };

    /**
     * @param {string | JsmlSlotNode | null} parent null for the root
     * @returns {{ children: JsmlTemplateNode[], closing: string | null }}
     */
    const parseChildren = (parent) => {
        /** @type {JsmlTemplateNode[]} */
        const out = [];
        let textStart = position;

        const flushText = () => {
            if (position > textStart) {
                pushText(source.substring(textStart, position), out);
            }
        };

        while (position < source.length) {
            if (source[position] !== "<" || !isTagStart()) {
                position++;
                continue;
            }

            flushText();

            if (source.startsWith("<!--", position)) {
                const end = source.indexOf("-->", position + 4);

                if (end === -1) {
                    fail("unterminated comment");
                }

                position = end + 3;
                textStart = position;
                continue;
            }

            if (source[position + 1] === "/") {
                const end = source.indexOf(">", position);

                if (end === -1) {
                    fail("unterminated closing tag");
                }

                const closing = source.substring(position + 2, end).trim();
                position = end + 1;

                if (parent === null) {
                    fail(`unexpected closing tag </${closing}>`);
                }

                return { children: out, closing };
            }

            out.push(parseElement());
            textStart = position;
        }

        flushText();

        if (parent !== null) {
            fail(`missing closing tag for <${typeof parent === "string" ? parent : "${…}"}>`);
        }

        return { children: out, closing: null };
    };

    /** @returns {JsmlTemplateNode} */
    const parseElement = () => {
        position++; // <

        if (source[position] === ">") {
            position++;
            const { children, closing } = parseChildren("");

            if (closing !== "") {
                fail(`fragment <> must be closed with </>, found </${closing}>`);
            }

            return { type: "fragment", children };
        }

        /** @type {string | JsmlSlotNode} */
        let tag;
        const index = readSlot();

        if (index !== undefined) {
            tag = { type: "slot", index };
        } else {
            tag = readUntil(/[\s>]/);

            if (tag === "") {
                fail("expected tag name");
            }
        }

        /** @type {JsmlTemplateProp[]} */
        const props = [];
        const selfClosing = parseProps(props);

        /** @type {JsmlElementNode} */
        const element = { type: "element", tag, props, children: [] };

        if (selfClosing || (typeof tag === "string" && JSML_VOID_TAGS.has(tag.toLowerCase()))) {
            return element;
        }

        const { children, closing } = parseChildren(tag);
        element.children = children;

        if (closing === "/") {
            return element; // <//> closes anything
        }

        if (typeof tag === "string") {
            if (closing !== tag) {
                fail(`expected </${tag}> but found </${closing}>`);
            }

            return element;
        }

        if (closing === "") {
            return element; // </> closes a component, like a fragment
        }

        const match = closing.match(/^(\d+)$/);
        if (match === null) {
            fail(`expected </\${…}>, </> or <//> to close a component, found </${closing}>`);
        }

        element.closeIndex = Number(match[1]);
        return element;
    };

    return parseChildren(null).children;
}

/**
 * @param {(string | JsmlSlotNode)[] | true} value
 * @param {any[]} values
 * @returns {any}
 */
function jsml_templateAttribute(value, values) {
    if (value === true) {
        return true;
    }

    if (value.length === 1 && typeof value[0] !== "string") {
        return values[value[0].index];
    }

    const isReactive = value.some(part => typeof part !== "string" && jsml_isReactive(values[part.index]));
    const join = () => value
        .map(part => typeof part === "string" ? part : String(read(values[part.index]) ?? ""))
        .join("");

    return isReactive
        ? join
        : join();
}

/**
 * @param {JsmlTemplateProp[]} templateProps
 * @param {any[]} values
 * @returns {Object<string, any>}
 */
function jsml_templateProps(templateProps, values) {
    const props = {};

    for (const prop of templateProps) {
        if (prop.kind === "spread") {
            Object.assign(props, values[prop.index]);
            continue;
        }

        // an expression inside the name, e.g. on:${EVENT_NAME}
        const name = prop.name.includes(JSML_SLOT_OPEN)
            ? jsml_splitSlots(prop.name)
                .map(part => typeof part === "string" ? part : String(values[part.index]))
                .join("")
            : prop.name;

        props[name] = jsml_templateAttribute(prop.value, values);
    }

    return props;
}

/**
 * @param {JsmlTemplateNode[]} nodes
 * @param {any[]} values
 * @param {boolean} isSvg
 * @returns {any}
 */
function jsml_instantiateList(nodes, values, isSvg) {
    if (nodes.length === 1) {
        return jsml_instantiate(nodes[0], values, isSvg);
    }

    return nodes.map(node => jsml_instantiate(node, values, isSvg));
}

/**
 * Instantiates top-down: a component runs before its children are created, so context works.
 *
 * @param {JsmlTemplateNode} node
 * @param {any[]} values
 * @param {boolean} isSvg
 * @returns {any}
 */
function jsml_instantiate(node, values, isSvg) {
    switch (node.type) {
        case "text":
            return node.value;
        case "slot":
            return values[node.index];
        case "fragment":
            return node.children.map(child => jsml_instantiate(child, values, isSvg));
    }

    let tag = node.tag;

    if (typeof tag !== "string") {
        tag = values[tag.index];

        if (node.closeIndex !== undefined && values[node.closeIndex] !== tag) {
            throw new SyntaxError(`html\`\`: closing tag </\${${values[node.closeIndex]?.name ?? "…"}}> does not match <\${${tag?.name ?? "…"}}>`);
        }

        if (typeof tag === "function") {
            const props = jsml_templateProps(node.props, values);

            if (node.children.length !== 0) {
                // lazy, like Solid: every access creates the children in the scope of whoever reads them
                Object.defineProperty(props, "children", {
                    get: () => jsml_instantiateList(node.children, values, isSvg),
                    enumerable: true,
                    configurable: true,
                });
            }

            return jsml_component(tag, props);
        }

        if (typeof tag !== "string") {
            throw new TypeError(`html\`\`: <\${…}> must be a component function or a tag name, got ${String(tag)}`);
        }
    }

    const elementIsSvg = isSvg || tag === "svg";
    const element = jsml_createElement(tag, elementIsSvg);
    const props = jsml_templateProps(node.props, values);
    const childrenAreSvg = elementIsSvg && tag !== "foreignObject";

    if (node.children.length === 0 && "children" in props) {
        jsml_append(element, props.children);
    }

    for (const child of node.children) {
        jsml_append(element, jsml_instantiate(child, values, childrenAreSvg));
    }

    jsml_spread(element, props, false);
    return element;
}

/**
 * JSX-like tagged template.
 *
 *   html`<div class="card" onClick=${handler}>${() => count()}</div>`
 *   html`<${Card} title=${title}>children</${Card}>`        (or close with </> or <//>)
 *   html`<${Show} when=${visible}>...</${Show}>`
 *   html`<input ...${props} disabled />`
 *   html`<>fragment</>`
 *
 * Returns a single node / value, or an array when there are multiple roots.
 *
 * @example html`<${Card} title="Hi ${name}"><p class=${cls} onClick=${fn}>${count}</p></>`
 *
 * @param {TemplateStringsArray} strings
 * @param {...JsmlTemplateValue} values
 * @returns {JsmlContent}
 */
function html(strings, ...values) {
    let tree = jsml_templateCache.get(strings);

    if (tree === undefined) {
        tree = jsml_parseTemplate(strings);
        jsml_templateCache.set(strings, tree);
    }

    return jsml_instantiateList(tree, values, false);
}

/**
 * @param {TemplateStringsArray | JsmlContent} first
 * @param {JsmlTemplateValue[]} values
 * @returns {JsmlContent}
 */
function jsml_templateOrContent(first, values) {
    // A tagged call passes the frozen strings array, which always has `raw`. Anything else is a value built earlier,
    // typically by html``, so that html.element(html`…`) keeps the editor's HTML support for the `html` tag.
    if (Array.isArray(first) && "raw" in first) {
        return html(/** @type {TemplateStringsArray} */ (first), ...values);
    }

    return /** @type {JsmlContent} */ (first);
}

/**
 * `html` for DOM APIs (`append`, `replaceWith`, `insertBefore`, …): always returns a `Node`. A single node is returned
 * as is, anything else (several roots, text, live values) is wrapped in a `DocumentFragment`. Live parts keep updating
 * after the fragment is inserted.
 *
 * Works as a tag or as a function over a `html` result. The function form keeps PhpStorm's HTML editing support,
 * which it gives only to templates tagged `html`.
 *
 * @example list.append(html.node`<li>${text}</li>`)
 * @example list.append(html.node(html`<li>${text}</li>`))
 *
 * @param {TemplateStringsArray | JsmlContent} strings
 * @param {...JsmlTemplateValue} values
 * @returns {Node}
 */
function jsml_htmlNode(strings, ...values) {
    const content = jsml_templateOrContent(strings, values);

    if (content instanceof Node) {
        return content;
    }

    const fragment = document.createDocumentFragment();
    jsml_append(fragment, content);
    return fragment;
}

/**
 * `html` for code that needs the element itself: returns the single root element and throws for any other shape.
 * Typed as `HTMLElement`; for an `<svg>` root cast it: `/** @type {SVGSVGElement} *\/ (html.element`<svg>…</svg>`)`.
 *
 * Works as a tag or as a function over a `html` result. The function form keeps PhpStorm's HTML editing support,
 * which it gives only to templates tagged `html`.
 *
 * @example const row = html.element`<tr><td>${name}</td></tr>`; row.dataset.id = id;
 * @example const row = html.element(html`<tr><td>${name}</td></tr>`);
 *
 * @param {TemplateStringsArray | JsmlContent} strings
 * @param {...JsmlTemplateValue} values
 * @returns {HTMLElement}
 */
function jsml_htmlElement(strings, ...values) {
    const content = jsml_templateOrContent(strings, values);

    if (!(content instanceof Element)) {
        const shape = Array.isArray(content) ? `${content.length} roots` : typeof content;
        throw new TypeError(`html.element: the template must have exactly one element root, got ${shape}`);
    }

    return /** @type {HTMLElement} */ (content);
}

html.node = jsml_htmlNode;
html.element = jsml_htmlElement;

// ---------------------------------------------------------------------------------------------------------------------
// jsml entry point
// ---------------------------------------------------------------------------------------------------------------------

/**
 * @returns {Jsml}
 */
function jsml_init() {
    /** @type {Map<string, Function>} */
    const factories = new Map();
    // only names that can not be HTML tags, `jsml.html()` still creates <html>
    const api = { define: jsml_define, Fragment, Show, For };

    return new Proxy(function jsml() {}, {
        apply(_target, _this, args) {
            return jsml_h(...args);
        },
        get(_target, tag) {
            if (typeof tag === "symbol" || tag === "then") {
                return undefined;
            }

            if (tag in api) {
                return api[tag];
            }

            const component = jsml_registry.get(tag);
            if (component !== undefined) {
                return (props, ...content) => jsml_component(component, jsml_componentProps(props, content));
            }

            if (/^[A-Z]/.test(tag)) {
                throw new ReferenceError(`jsml: component <${tag}> is not defined, register it with jsml.define({ ${tag} })`);
            }

            let factory = factories.get(tag);
            if (factory === undefined) {
                factory = (props, ...content) => jsml_element(tag, props, content);
                factories.set(tag, factory);
            }

            return factory;
        }
    });
}

/**
 * Element factories (`jsml.div(props, ...content)`), `jsml(Component, props, ...children)`, `jsml.define`,
 * `jsml.Show`, `jsml.For` and registered components (`jsml.Card(...)`).
 */
const jsml = jsml_init();
/** Placeholder for "no props": `jsml.p(_, "text")`. */
const _ = undefined;
