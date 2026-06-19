declare type MarkDownTokenType =
    "PARAGRAPH"
    | "TEXT"
    | "WHITESPACE"
    | "NEW_LINE"
    | "HEADING"
    | "HORIZONTAL_LINE"
    | "DECORATION"
    | "DECORATION_START"
    | "DECORATION_END"
    | "LIST_ITEM"
    | "EXCLAMATION"
    | "BRACKET_START"
    | "BRACKET_END"
    | "PARENTHESIS_START"
    | "PARENTHESIS_END"
    | "QUOTE"
    | "QUOTE_BLOCK"
    | "CODE"
    | "CODE_BLOCK"
    | "HTML_START"
    | "HTML_END"

declare type MarkDownToken = {
    type: MarkDownTokenType,
    literal: string,
}

declare type MarkDownTextNode = {
    type: "TEXT",
    text: string
}

declare type MarkDownHeadingNode = {
    type: "HEADING",
    level: number,
    ast: MarkDownAst
}

declare type MarkDownHorizontalLineNode = {
    type: "HORIZONTAL_LINE"
}

declare type MarkDownParagraphNode = {
    type: "PARAGRAPH",
    ast: MarkDownAst
}

declare type MarkDownListItemNode = {
    type: "ORDERED" | "UNORDERED",
    indent: number,
    ast: MarkDownAst
}

declare type MarkDownListNode = {
    type: "LIST",
    items: MarkDownListItemNode[]
}

declare type MarkDownQuoteNode = {
    type: "QUOTE",
    indent: number,
    ast: MarkDownAst
}

declare type MarkDownCodeNode = {
    type: "CODE",
    code: string
}

declare type MarkDownCodeBlockNode = {
    type: "CODE_BLOCK",
    code: string,
}

declare type MarkDownHtmlNode = {
    type: "HTML",
    html: string,
}

declare type MarkDownLinkNode = {
    type: "LINK",
    href: string,
    label: MarkDownAst,
    labelText: string,
    title?: string,
}

declare type MarkDownImageNode = {
    type: "IMAGE",
    src: string,
    alt: string,
    title?: string,
}

declare type MarkDownNewLineNode = {
    type: "NEW_LINE",
}

declare type MarkDownDecorationNode = {
    type: "DECORATION",
    style: "ITALIC" | "BOLD" | "ITALIC-BOLD" | string,
    ast: MarkDownAst
}

declare type MarkDownAstNode =
    MarkDownNewLineNode
    | MarkDownTextNode
    | MarkDownDecorationNode
    | MarkDownHeadingNode
    | MarkDownHorizontalLineNode
    | MarkDownParagraphNode
    | MarkDownListNode
    | MarkDownListItemNode
    | MarkDownCodeNode
    | MarkDownCodeBlockNode
    | MarkDownHtmlNode
    | MarkDownQuoteNode
    | MarkDownLinkNode
    | MarkDownImageNode;

declare type MarkDownAst = MarkDownAstNode[];

declare type OnElementCreated = (element: HTMLElement, node: MarkDownAstNode) => void;



declare type MarkdownGalleryImage = {
    src: string,
    alt: string,
    title?: string
}