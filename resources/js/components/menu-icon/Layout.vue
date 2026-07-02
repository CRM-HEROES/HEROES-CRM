<template>
    <div class="hc-flex-column" style="height: 100%">
        <div class="hc-flex-column hc-flex-1" style="overflow: hidden">
            <item-list class="hc-flex-1" padding="5px" style="overflow: auto">
                <menu-icon-row
                    v-for="menuIcon in menuIcons"
                    :key="menuIcon.key"
                    :menu-icon="menuIcon"
                />
            </item-list>
        </div>
    </div>
</template>

<script>
// Components
import MenuIconRow from "./MenuIconRow.vue";

export default {
    components: {
        MenuIconRow,
    },

    data() {
        return {
            menuIcons: [
                {
                    key: "prospects",
                    group: "Entête",
                    name: "Prospects",
                },
                {
                    key: "events",
                    group: "Entête",
                    name: "Agendas",
                },
                {
                    key: "map",
                    group: "Entête",
                    name: "Map",
                },
                {
                    key: "mails",
                    group: "Entête",
                    name: "Mails",
                },
                {
                    key: "orders",
                    group: "Entête",
                    name: "Devis",
                },
                {
                    key: "stats",
                    group: "Entête",
                    name: "Statistiques",
                },
                {
                    key: "documents",
                    group: "Entête",
                    name: "Documents",
                },
                {
                    key: "campaigns",
                    group: "Entête",
                    name: "Campagnes",
                },
            ],
        };
    },
};
</script>
