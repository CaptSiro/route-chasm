declare type MarkDownTokenType =
    "PARAGRAPH" //
    | "TEXT" //
    | "WHITESPACE" //
    | "NEW_LINE" //
    | "HEADING" //
    | "DECORATION"
    | "DECORATION_START"
    | "DECORATION_END"
    | "LIST_ITEM" //
    | "EXCLAMATION" //
    | "BRACKET_START" //
    | "BRACKET_END" //
    | "PARENTHESIS_START" //
    | "PARENTHESIS_END" //
    | "QUOTE" //
    | "QUOTE_BLOCK" //
    | "CODE" //
    | "CODE_BLOCK" //

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
    ast: MarkDownAstNode[]
}

declare type MarkDownParagraphNode = {
    type: "PARAGRAPH",
    ast: MarkDownAstNode[]
}

declare type MarkDownListItemNode = {
    type: "ORDERED" | "UNORDERED",
    indent: number,
    ast: MarkDownAstNode[]
}

declare type MarkDownListNode = {
    type: "LIST",
    items: MarkDownListItemNode[]
}

declare type MarkDownQuoteNode = {
    type: "QUOTE",
    indent: number,
    ast: MarkDownAstNode[]
}

declare type MarkDownCodeNode = {
    type: "CODE",
    code: string
}

declare type MarkDownCodeBlockNode = {
    type: "CODE_BLOCK",
    code: string,
}

declare type MarkDownLinkNode = {
    type: "LINK",
    href: string,
    label: MarkDownAstNode[],
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
    ast: MarkDownAstNode[]
}

declare type MarkDownAstNode =
    MarkDownNewLineNode
    | MarkDownTextNode
    | MarkDownDecorationNode
    | MarkDownHeadingNode
    | MarkDownParagraphNode
    | MarkDownListNode
    | MarkDownCodeNode
    | MarkDownCodeBlockNode
    | MarkDownQuoteNode
    | MarkDownLinkNode
    | MarkDownImageNode;