<template>
    <div>
        <item @click="$emit('back')" style="border-bottom: 1px solid #eee">
            <icon class="fa fa-caret-left" />
            <div
                class="hc-item-main-content hc-flex-column"
                v-text="$t('event.choose_event_description_template')"
            ></div>
        </item>
        <search v-model="messageTemplateKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <event-description-template-row
                v-for="eventDescriptionTemplate in filteredEventDescriptionTemplates"
                :key="eventDescriptionTemplate.id"
                :template="eventDescriptionTemplate"
                @click="
                    $emit(
                        'event-description-template-selected',
                        eventDescriptionTemplate
                    )
                "
            />
        </item-list>
        <buttons v-if="can('all.project.event-description-template.add')">
            <a
                @click.prevent="addEventDescriptionTemplate"
                v-text="$t('add')"
            ></a>
        </buttons>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { OPEN_MODAL } from "@/actions/modal";
import { FETCH_EVENT_DESCRIPTION_TEMPLATES } from "@/actions/project/event-description-template";

// Components
import EventDescriptionTemplateRow from "./EventDescriptionTemplateRow.vue";

export default {
    components: {
        EventDescriptionTemplateRow,
    },

    data() {
        return {
            eventDescriptionTemplateKeyword: "",
        };
    },

    mounted() {
        this.fetchEventDescriptionTemplates();
    },

    methods: {
        /**
         *
         */
        addEventDescriptionTemplate() {
            store.commit(OPEN_MODAL, "event-description-template-add");
        },

        /**
         *
         */
        fetchEventDescriptionTemplates() {
            store.dispatch(FETCH_EVENT_DESCRIPTION_TEMPLATES);
        },
    },

    computed: {
        ...mapGetters(["eventDescriptionTemplates", "can"]),

        /**
         *
         */
        filteredEventDescriptionTemplates() {
            const keyword = removeStringAccent(
                this.eventDescriptionTemplateKeyword
            );

            return this.eventDescriptionTemplates.filter(
                (eventDescriptionTemplate) =>
                    removeStringAccent(eventDescriptionTemplate.name).indexOf(
                        keyword
                    ) >= 0
            );
        },
    },
};
</script>
