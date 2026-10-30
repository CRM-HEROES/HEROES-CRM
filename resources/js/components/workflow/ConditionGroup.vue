<template>
    <div class="hc-wfc" :class="'depth-' + depth">
        <p v-if="depth === 1" class="hc-wfc-intro" v-text="$t('workflow.condition.intro')"></p>
        <div class="hc-wfc-head">
            <select :value="modelValue.op || 'and'" :disabled="readOnly" @change="update({ op: $event.target.value })">
                <option value="and" v-text="$t('workflow.condition.all')"></option>
                <option value="or" v-text="$t('workflow.condition.any')"></option>
            </select>
            <button v-if="depth > 1 && !readOnly" type="button" class="hc-wfc-remove" :title="$t('workflow.condition.remove_group')" @click="$emit('remove')">×</button>
        </div>

        <p v-if="!items.length" class="hc-wfc-empty" v-text="$t('workflow.condition.empty')"></p>

        <div v-for="(item, index) in items" :key="index" class="hc-wfc-item">
            <!-- A group inside the group -->
            <condition-group
                v-if="item.items"
                :model-value="item"
                :subject="subject"
                :catalog="catalog"
                :read-only="readOnly"
                :depth="depth + 1"
                @update:model-value="setItem(index, $event)"
                @remove="removeItem(index)"
            />

            <!-- Date / time check -->
            <div v-else-if="item.check" class="hc-wfc-row">
                <select :value="item.check" :disabled="readOnly" @change="setItem(index, newCheck($event.target.value))">
                    <option v-for="check in catalog.conditions.checks" :key="check.name" :value="check.name" v-text="$t('workflow.condition.check.' + check.name)"></option>
                </select>
                <div v-for="field in checkFields(item.check)" :key="field.key" class="hc-wfc-field">
                    <label v-text="$t('workflow.condition.field.' + field.key)"></label>
                    <field-input
                        :model-value="item[field.key]"
                        :type="field.type"
                        :read-only="readOnly"
                        @update:model-value="setItem(index, { ...item, [field.key]: $event })"
                    />
                </div>
                <p class="hc-wfc-help" v-text="$t('workflow.condition.timezone_help')"></p>
            </div>

            <!-- Criterion on the prospect -->
            <div v-else class="hc-wfc-row">
                <select :value="filterName(item)" :disabled="readOnly" @change="setItem(index, newFilter($event.target.value))">
                    <option v-if="!known(filterName(item))" :value="filterName(item)" v-text="filterName(item)"></option>
                    <option v-for="filter in filters" :key="filter.name" :value="filter.name" v-text="$t('workflow.condition.filter.' + filter.name)"></option>
                </select>
                <field-input
                    v-if="known(filterName(item)) && filterType(item) !== 'boolean'"
                    :model-value="item.filters[filterName(item)]"
                    :type="filterType(item)"
                    :read-only="readOnly"
                    @update:model-value="setItem(index, { filters: { [filterName(item)]: $event } })"
                />
                <select
                    v-else-if="known(filterName(item))"
                    :value="item.filters[filterName(item)] ? 'true' : 'false'"
                    :disabled="readOnly"
                    @change="setItem(index, { filters: { [filterName(item)]: $event.target.value === 'true' } })"
                >
                    <option value="true" v-text="$t('workflow.yes')"></option>
                    <option value="false" v-text="$t('workflow.no')"></option>
                </select>
            </div>

            <button v-if="!readOnly && !item.items" type="button" class="hc-wfc-remove" :title="$t('workflow.condition.remove')" @click="removeItem(index)">×</button>
        </div>

        <div v-if="!readOnly" class="hc-wfc-add">
            <button v-if="filters.length" type="button" @click="add(newFilter(filters[0].name))" v-text="'+ ' + $t('workflow.condition.add_filter')"></button>
            <button type="button" @click="add(newCheck('work_hours'))" v-text="'+ ' + $t('workflow.condition.add_check')"></button>
            <button v-if="depth < catalog.conditions.max_depth" type="button" @click="add({ op: 'and', items: [] })" v-text="'+ ' + $t('workflow.condition.add_group')"></button>
        </div>
    </div>
</template>

<style>
.hc-wfc { position: relative; padding: 10px; background: #f6f7fb; border: 1px solid #e3e6ef; border-radius: 10px; }
.hc-wfc .hc-wfc { margin-top: 4px; background: #fff; border-left: 3px solid #5b6cf0; }
.hc-wfc select { padding: 7px 10px; color: #2b2f3a; background: #fff; border: 1px solid #d5d9e4; border-radius: 8px; font-size: 13px; }
.hc-wfc select:focus { border-color: #5b6cf0; box-shadow: 0 0 0 3px rgba(91, 108, 240, 0.15); outline: 0; }
.hc-wfc-intro { margin: 0 0 8px; color: #5d6272; font-size: 12px; }
.hc-wfc-empty { margin: 4px 0 8px; padding: 8px 10px; background: #fff7dd; border-radius: 8px; color: #6b4b00; font-size: 12px; }
.hc-wfc-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.hc-wfc-item { position: relative; margin-bottom: 8px; padding: 8px 30px 8px 8px; background: #fff; border: 1px solid #e3e6ef; border-radius: 8px; }
.hc-wfc .hc-wfc .hc-wfc-item { background: #f6f7fb; }
.hc-wfc-row { display: flex; flex-direction: column; gap: 6px; }
.hc-wfc-row select { width: 100%; }
.hc-wfc-field label { font-size: 11px; color: #7a7f90; }
.hc-wfc-help { margin: 0; color: #8a8fa0; font-size: 11px; }
.hc-wfc-remove { position: absolute; top: 6px; right: 6px; width: 22px; height: 22px; padding: 0; color: #8a8fa0; background: transparent; border: 0; border-radius: 50%; font-size: 16px; line-height: 1; cursor: pointer; }
.hc-wfc-remove:hover { color: #c0341d; background: #fdecea; }
.hc-wfc-head .hc-wfc-remove { position: static; }
.hc-wfc-add { display: flex; flex-wrap: wrap; gap: 6px; }
.hc-wfc-add button { padding: 5px 12px; color: #3a49c9; background: #eceefd; border: 1px dashed #aab2f5; border-radius: 16px; font-size: 12px; font-weight: 600; cursor: pointer; transition: background 0.15s; }
.hc-wfc-add button:hover { background: #dde1fb; }
</style>

<script>
import FieldInput from "./FieldInput.vue";
import { filtersFor, newCheckItem, newFilterItem } from "@/utils/workflow-conditions";

export default {
    name: "ConditionGroup",

    components: { FieldInput },

    props: {
        // { op: "and" | "or", items: [ { filters } | { check, ... } | { op, items } ] }
        modelValue: { type: Object, required: true },
        // prospect | order | event: the criteria depend on it
        subject: { type: String, default: "prospect" },
        catalog: { type: Object, required: true },
        readOnly: { type: Boolean, default: false },
        depth: { type: Number, default: 1 },
    },

    emits: ["update:modelValue", "remove"],

    computed: {
        items() {
            return this.modelValue.items || [];
        },

        filters() {
            return filtersFor(this.catalog, this.subject);
        },
    },

    methods: {
        update(patch) {
            this.$emit("update:modelValue", { ...this.modelValue, items: this.items, ...patch });
        },

        setItem(index, item) {
            this.update({ items: this.items.map((current, i) => (i === index ? item : current)) });
        },

        removeItem(index) {
            this.update({ items: this.items.filter((_, i) => i !== index) });
        },

        add(item) {
            this.update({ items: [...this.items, item] });
        },

        filterName(item) {
            return Object.keys(item.filters || {})[0];
        },

        known(name) {
            return this.filters.some((filter) => filter.name === name);
        },

        filterType(item) {
            return (this.filters.find((filter) => filter.name === this.filterName(item)) || {}).type;
        },

        newFilter(name) {
            return newFilterItem(this.catalog, this.subject, name);
        },

        newCheck(name) {
            return newCheckItem(name);
        },

        checkFields(name) {
            return (this.catalog.conditions.checks.find((check) => check.name === name) || {}).fields || [];
        },
    },
};
</script>
