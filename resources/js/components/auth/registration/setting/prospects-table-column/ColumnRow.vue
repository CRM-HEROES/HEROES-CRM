<template>
    <label
        :class="[
            'hc-user-registration-column-row',
            !column.hidden || column.hidden === undefined ? '' : 'hidden',
        ]"
        @click.stop="
            newColumn || column.hidden
                ? $emit('add', column)
                : $emit('remove', column)
        "
    >
        <div
            class="hc-user-registration-column-icon"
            :style="{ backgroundColor: iconColor + '22' }"
        >
            <i :class="icon" :style="{ color: iconColor }"></i>
        </div>
        <div class="hc-user-registration-column-info">
            <span class="hc-user-registration-column-name" v-text="column.name">
            </span>
            <p
                class="hc-user-registration-column-description"
                v-text="description"
            ></p>
        </div>
        <div class="hc-user-registration-column-move">
            <i class="fa fa-bars"></i>
        </div>
        <div
            v-if="!newColumn"
            :class="[
                'hc-user-registration-column-pin',
                column.fixed ? 'fixed' : '',
            ]"
            @click.stop="$emit('toggle-pin', column)"
        >
            <i class="fa fa-thumbtack"></i>
        </div>
        <div v-if="newColumn" class="hc-user-registration-column-plus">
            <i class="fa fa-plus"></i>
        </div>
        <div v-else class="hc-user-registration-column-check"></div>
    </label>
</template>

<style>
.hc-user-registration-column-row {
    display: flex;
    flex-direction: row;
    padding: 8px 20px;
    gap: 16px;
    border-top: 1px solid #eee;
    margin-bottom: -1px;
    align-items: center;
    cursor: pointer;
}

.hc-user-registration-column-row:hover {
    background-color: #f5f5f5;
}

.hc-user-registration-column-icon {
    width: 40px;
    height: 40px;
    text-align: center;
    line-height: 40px;
    border-radius: 50%;
    font-size: 16px;
    opacity: 0.6;
}

.hc-user-registration-column-info {
    display: flex;
    flex-direction: column;
    flex: 1;
}

.hc-user-registration-column-name {
    font-size: 12px;
    font-weight: 500;
}

.hc-user-registration-column-description {
    margin-top: 0px;
    font-size: 12px;
    color: #6c6c6c;
    margin-bottom: 0;
}

.hc-user-registration-column-move,
.hc-user-registration-column-pin {
    width: 30px;
    height: 40px;
    text-align: center;
    line-height: 40px;
    color: #aaa;
}

.hc-user-registration-column-pin {
    font-size: 16px;
}

.hc-user-registration-column-pin.fixed {
    color: #49bde1;
}

.hc-user-registration-column-plus {
    width: 20px;
    text-align: center;
    line-height: 40px;
    vertical-align: middle;
    font-size: 16px;
    color: #aaaaaa;
}

.hc-user-registration-column-check {
    width: 20px;
    text-align: center;
    line-height: 40px;
    vertical-align: middle;
}

.hc-user-registration-column-check:before {
    content: "";
    width: 16px;
    height: 16px;
    border-radius: 4px;
    border: 1px solid #49bde1;
    background-color: #49bde1;
    display: inline-block;
    vertical-align: text-top;
}

.hidden > .hc-user-registration-column-check:before {
    border: 1px solid #bbb;
    background-color: #ffffff;
}
</style>

<script>
export default {
    props: {
        column: {
            type: String,
        },
        newColumn: {
            type: Boolean,
        },
    },

    methods: {
        change(event) {
            this.$emit("change", event);
            this.$emit("update:modelValue", event.target.value);
        },
    },

    computed: {
        icon() {
            switch (this.column.columnId) {
                case "events":
                    return "fa fa-calendar";

                case "interactions":
                    return "fa fa-phone";

                case "sms":
                    return "fa fa-comments";

                case "orders":
                    return "fa fa-shopping-bag";

                case "created_at":
                    return "fa fa-calendar";

                default:
                    if (this.column.type == "meta-field") {
                        return "fa fa-plus-square";
                    }

                    if (this.column.type == "category") {
                        return "fa fa-tags";
                    }

                    if (this.column.type == "thread") {
                        return "fa fa-envelope";
                    }

                    return "fa fa-columns";
            }
        },

        description() {
            if (this.column.description) {
                return this.column.description;
            }

            switch (this.column.columnId) {
                case "events":
                    return "Liste des RDVs du prospect";

                case "interactions":
                    return "Liste des appels du prospect";

                case "sms":
                    return "Liste des SMS envoyés du prospect";

                case "orders":
                    return "Liste des devis du prospect";

                default:
                    if (this.column.type == "meta-field") {
                        return "Autre champ";
                    }

                    if (this.column.type == "category") {
                        return "Catégorie de filtre";
                    }

                    if (this.column.type == "thread") {
                        return "Canal de discussion";
                    }

                    return "Champ par défaut";
            }
        },

        iconColor() {
            switch (this.column.columnId) {
                case "events":
                    return "#9e3cda";
                case "interactions":
                    return "#e5961e";
                case "sms":
                    return "#da3c58";
                case "orders":
                    return "#329bd7";
                default:
                    if (this.column.type == "meta-field") {
                        return "#489f1f";
                    }

                    if (this.column.type == "category") {
                        return "#ed5e3e";
                    }

                    if (this.column.type == "thread") {
                        return "#489f1f";
                    }

                    return "#1e6ee5";
            }
        },
    },
};
</script>
