const TIME_BUILDER = 'time';

class TimeWidget extends StartuhWidget {
    /** @type {TimeWidgetConfig} */
    #config;

    constructor(config) {
        super();
        this.#config = config;
    }

    instantiate() {
        return startuh_WidgetElement(this, "Widget", this.#config);
    }

    inspect() {
        return [];
    }

    save() {
        const ret = super.save();

        ret.builder = TIME_BUILDER;
        ret.showDate = true;

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
