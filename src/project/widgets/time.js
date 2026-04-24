const TIME_BUILDER = 'time';

const TIME_DAY_NAMES = ["SUN", "MON", "TUE", "WED", "THU", "FRI", "SAT"];
const TIME_MONTH_NAMES = ["JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC"];

function time_parseDate(date) {
    const day = date.getDate();
    let suffix;

    switch (day % 10) {
        case 1:
            suffix = "st";
            break;

        case 2:
            suffix = "nd";
            break;

        case 3:
            suffix = "rd";
            break;

        default:
            suffix = "th";
            break;
    }

    return {
        dayName: TIME_DAY_NAMES[date.getDay()],
        day,
        suffix,
        monthName: TIME_MONTH_NAMES[date.getMonth()]
    };
}

function time_parseTime(date) {
    const hours = date.getHours();
    let hoursPeriod = hours;
    let period = "AM";

    if (hours > 12) {
        hoursPeriod -= 12;
        period = "PM";
    }
    if (hours === 0) {
        hoursPeriod = 12;
        period = "AM";
    }

    let minutes = date.getMinutes();
    if (minutes / 10 < 1) {
        minutes = "0" + minutes;
    }

    return {
        hours,
        hoursPeriod,
        minutes,
        period
    };
}



class TimeWidget extends StartuhWidget {
    /** @type {TimeWidgetConfig} */
    #config;
    /** @type {HTMLElement} */
    #display;
    #interval;



    constructor(config) {
        super();

        this.#config = config;
        this.setConfig(config);

        this.#display = jsml.div("w-time");

        // update clock
        this.#interval = setInterval(() => {
            const date = new Date();
            if (date.getSeconds() !== 0) {
                return;
            }

            this.updateDisplay(date);
        }, 1000);

        this.updateDisplay();
    }



    /**
     * @param {Date | undefined} date
     */
    updateDisplay(date = undefined) {
        date ??= new Date();
        this.#display.textContent = "";

        jsml_addContent(this.#display, [
            this.displayTime(date),
            Optional(this.#config.showDate, this.displayDate(date))
        ]);
    }

    displayDate(date) {
        const time = time_parseDate(date);

        return jsml.div("date", [
            jsml.span("week-day day", time.dayName),
            jsml.span("day", time.day + time.suffix),
            jsml.span("month", time.monthName)
        ]);
    }

    displayTime(date) {
        const time = time_parseTime(date);

        return jsml.div("time", [
            jsml.span("hours", this.#config.isMilitaryTime ? time.hours : time.hoursPeriod),
            jsml.span("minutes", time.minutes),
            Optional(this.#config.showPeriod, time.period)
        ]);
    }

    instantiate() {
        return startuh_WidgetElement(this, this.#display, this.#config);
    }

    inspect() {
        return [
            TitleInspector("Time"),

            HRInspector(),

            CheckboxInspector(this.#config.showDate ?? false, value => {
                this.#config.showDate = value;
                this.updateDisplay();
                startuh_save();
                return true;
            }, "Show date"),

            CheckboxInspector(this.#config.showPeriod ?? false, value => {
                this.#config.showPeriod = value;
                this.updateDisplay();
                startuh_save();
                return true;
            }, "Show AM/PM"),

            CheckboxInspector(this.#config.isMilitaryTime ?? false, value => {
                this.#config.isMilitaryTime = value;
                this.updateDisplay();
                startuh_save();
                return true;
            }, "Show as military time"),
        ];
    }

    save() {
        const ret = {
            ...this.#config,
            ...super.save()
        };

        ret.builder = TIME_BUILDER;

        return ret;
    }
}



const time_builder = new FunctionalStartuhBuilder(
    TIME_BUILDER,
    config => new TimeWidget(config)
);



startuh_addBuilder(time_builder);
startuh_addPrefab(
    startuh_PrefabElement(time_builder, Icon("nf-fa-clock"), "Time")
);
