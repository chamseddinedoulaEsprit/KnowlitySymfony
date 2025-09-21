// mychart_controller.js

import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.element.addEventListener('chartjs:pre-connect', this._onPreConnect);
        this.element.addEventListener('chartjs:connect', this._onConnect);
    }

    disconnect() {
        // You should always remove listeners when the controller is disconnected to avoid side effects
        this.element.removeEventListener('chartjs:pre-connect', this._onPreConnect);
        this.element.removeEventListener('chartjs:connect', this._onConnect);
    }

    _onPreConnect(event) {
        // The chart is not yet created
        // You can access the config that will be passed to "new Chart()"
        console.log(event.detail.config);

        // For instance you can format Y axis
        // To avoid overriding existing config, you should distinguish 3 cases:
        // # 1. No existing scales config => add a new scales config
        event.detail.config.options.scales = {
            y: {
                ticks: {
                    callback: function (value, index, values) {
                        /* ... */
                    },
                },
            },
        };
        // # 2. Existing scales config without Y axis config => add new Y axis config
        event.detail.config.options.scales.y = {
            ticks: {
                callback: function (value, index, values) {
                    /* ... */
                },
            },
        };
        // # 3. Existing Y axis config => update it
        event.detail.config.options.scales.y.ticks = {
            callback: function (value, index, values) {
                /* ... */
            },
        };
    }

    _onConnect(event) {
        // The chart was just created
        console.log(event.detail.chart); // You can access the chart instance using the event details

        // For instance you can listen to additional events
        event.detail.chart.options.onHover = (mouseEvent) => {
            /* ... */
        };
        event.detail.chart.options.onClick = (mouseEvent) => {
            /* ... */
        };
    }
}