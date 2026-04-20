import { StartuhWidgetConfig } from "../startuh";



declare type TimeWidgetConfig = {
    isMilitaryTime?: boolean,
    showPeriod?: boolean,
    showDate?: boolean,
} & StartuhWidgetConfig;