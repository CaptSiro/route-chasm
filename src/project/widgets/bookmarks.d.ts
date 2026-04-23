import { StartuhWidgetConfig } from "../startuh";



declare type Bookmark = {
    link: string,
    title: string,
    isTitleIcon: boolean,
    color: string,
};

declare type BookmarksWidgetConfig = {
    items: Bookmark[],
} & StartuhWidgetConfig;