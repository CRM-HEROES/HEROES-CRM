<template>
    <modal
        :name="name"
        :title="$t('document.field.order.table.edit')"
        :width="300"
        @open="setContent"
    >
        <tab-layout :count="3" :tab="tab" class="hc-flex-1">
            <template #1>
                <div class="hc-flex-column" style="height: 100%">
                    <tab :count="2" @tab:change="(t) => (columnTab = t)">
                        <template #1>{{
                            $t("document.field.order.table.columns")
                        }}</template>
                        <template #2>{{
                            $t("document.field.order.table.summaries")
                        }}</template>
                    </tab>
                    <tab-layout class="hc-flex-1" :count="2" :tab="columnTab">
                        <!-- Columns -->

                        <template #1>
                            <div class="hc-flex-column" style="height: 100%">
                                <draggable
                                    v-if="content"
                                    tag="item-list"
                                    class="hc-flex-1"
                                    :list="content.columns"
                                    item-key="header.name"
                                    @end="updateField"
                                >
                                    <template #item="{ element }">
                                        <column-row
                                            :column="element"
                                            @click.prevent="setColumn(element)"
                                        />
                                    </template>
                                </draggable>
                                <buttons>
                                    <button
                                        v-text="$t('add')"
                                        @click.prevent="
                                            (column = addColumn()),
                                                (summary = null),
                                                (tab = 2),
                                                (columnEditTab = 1)
                                        "
                                    ></button>
                                </buttons>
                            </div>
                        </template>

                        <!-- Summaries -->

                        <template #2>
                            <div class="hc-flex-column" style="height: 100%">
                                <draggable
                                    v-if="content"
                                    tag="item-list"
                                    class="hc-flex-1"
                                    :list="content.summaries"
                                    item-key="header.name"
                                    @end="updateField"
                                >
                                    <template #item="{ element }">
                                        <summary-row
                                            :summary="element"
                                            @click.prevent="setSummary(element)"
                                        />
                                    </template>
                                </draggable>
                                <buttons>
                                    <button
                                        v-text="$t('add')"
                                        @click.prevent="
                                            (summary = addSummary()),
                                                (column = null),
                                                (tab = 2),
                                                (columnEditTab = 1)
                                        "
                                    ></button>
                                </buttons></div
                        ></template>
                    </tab-layout>
                </div>
            </template>

            <!-- Settings -->

            <template #2>
                <!-- Column settings -->

                <div class="hc-flex-column" style="height: 100%" v-if="column">
                    <!-- Header name -->

                    <item @click="tab = 0" class="bordered">
                        <icon class="fa fa-caret-left" />
                        <div
                            class="hc-item-main-content"
                            v-text="column.header.content"
                        ></div>
                    </item>

                    <item-list class="hc-flex-1" padding="5px">
                        <!-- Header content -->

                        <v-field :label="$t('name')" required v-slot="{ label }"
                            ><input
                                :placeholder="label + ' ...'"
                                v-model="column.header.content"
                                @change="updateField"
                        /></v-field>

                        <!-- Header style -->

                        <item
                            @click="
                                (tab = 2),
                                    (columnEditTab = 0),
                                    (selectedField = column.header)
                            "
                        >
                            <icon class="fa fa-bold" />
                            <div
                                class="hc-item-main-content"
                                v-text="
                                    $t(
                                        'document.style.order_table.table.header_style'
                                    )
                                "
                            ></div>
                            <icon class="fa fa-caret-right" />
                        </item>

                        <!-- Body content -->

                        <item @click="(tab = 2), (columnEditTab = 1)">
                            <icon class="fa fa-align-left" />
                            <div
                                class="hc-item-main-content"
                                v-text="
                                    $t(
                                        'document.style.order_table.table.content'
                                    )
                                "
                            ></div>
                            <icon class="fa fa-caret-right" />
                        </item>

                        <!-- Body style -->

                        <item
                            @click="
                                (tab = 2),
                                    (columnEditTab = 0),
                                    (selectedField = column.body)
                            "
                        >
                            <icon class="fa fa-columns" />
                            <div
                                class="hc-item-main-content"
                                v-text="
                                    $t(
                                        'document.style.order_table.table.body_style'
                                    )
                                "
                            ></div>
                            <icon class="fa fa-caret-right" />
                        </item>
                    </item-list>

                    <!-- Delete column -->

                    <buttons>
                        <a
                            class="hc-button-danger"
                            v-text="$t('delete')"
                            @click.prevent="removeColumn"
                        ></a>
                    </buttons>
                </div>

                <!-- Summary settings -->

                <div
                    class="hc-flex-column"
                    style="height: 100%"
                    v-else-if="summary"
                >
                    <!-- Summary name -->

                    <item @click="tab = 0" class="bordered">
                        <icon class="fa fa-caret-left" />
                        <div
                            class="hc-item-main-content"
                            v-text="summary.header.content"
                        ></div>
                    </item>

                    <item-list class="hc-flex-1" padding="5px">
                        <!-- Header content -->

                        <v-field :label="$t('name')" required v-slot="{ label }"
                            ><input
                                :placeholder="label + ' ...'"
                                v-model="summary.header.content"
                                @change="updateField"
                        /></v-field>

                        <!-- Header style -->

                        <item
                            @click="
                                (tab = 2),
                                    (columnEditTab = 0),
                                    (selectedField = summary.header)
                            "
                        >
                            <icon class="fa fa-bold" />
                            <div
                                class="hc-item-main-content"
                                v-text="
                                    $t(
                                        'document.style.order_table.summary.header_style'
                                    )
                                "
                            ></div>
                            <icon class="fa fa-caret-right" />
                        </item>

                        <!-- Body content -->

                        <item @click="(tab = 2), (columnEditTab = 1)">
                            <icon class="fa fa-align-left" />
                            <div
                                class="hc-item-main-content"
                                v-text="
                                    $t(
                                        'document.style.order_table.summary.content'
                                    )
                                "
                            ></div>
                            <icon class="fa fa-caret-right" />
                        </item>

                        <!-- Body style -->

                        <item
                            @click="
                                (tab = 2),
                                    (columnEditTab = 0),
                                    (selectedField = summary.body)
                            "
                        >
                            <icon class="fa fa-columns" />
                            <div
                                class="hc-item-main-content"
                                v-text="
                                    $t(
                                        'document.style.order_table.summary.body_style'
                                    )
                                "
                            ></div>
                            <icon class="fa fa-caret-right" />
                        </item>
                    </item-list>

                    <!-- Delete summary -->

                    <buttons>
                        <a
                            class="hc-button-danger"
                            v-text="$t('delete')"
                            @click.prevent="removeSummary"
                        ></a>
                    </buttons>
                </div>
            </template>

            <!--  -->

            <template #3>
                <frame-layout
                    class="hc-flex-column"
                    style="height: 100%"
                    v-if="column || summary"
                    :tab="columnEditTab"
                    :count="3"
                >
                    <!-- Style -->

                    <template #1>
                        <div
                            class="hc-flex-column"
                            style="height: 100%"
                            v-if="selectedField"
                        >
                            <!-- Name -->

                            <item @click="tab = 1" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        column
                                            ? column.header.content
                                            : summary.header.content
                                    "
                                ></div>
                            </item>

                            <!-- Preview style -->

                            <div
                                style="
                                    display: flex;
                                    flex-direction: column;
                                    align-items: center;
                                    justify-content: center;
                                    width: 100%;
                                    min-height: 80px;
                                    background-color: #eee;
                                "
                            >
                                <div
                                    v-text="
                                        column
                                            ? column.header.content
                                            : summary.header.content
                                    "
                                    :style="{
                                        ...selectedField.style,

                                        display: 'flex',
                                        'flex-direction': 'column',
                                        'justify-content': 'center',
                                    }"
                                ></div>
                            </div>

                            <!-- Frame -->

                            <div class="hc-flex-1" style="overflow: auto">
                                <tree-layout
                                    class="hc-document-styles-bloc"
                                    :open="true"
                                >
                                    <template #header>
                                        <item>
                                            <icon
                                                class="fa fa-square"
                                                color="#333333"
                                            />
                                            <div
                                                class="hc-item-main-content hc-document-styles-bloc-title"
                                                v-text="
                                                    $t(
                                                        'document.style.frame.title'
                                                    )
                                                "
                                            ></div>
                                            <icon class="fa fa-caret-down" />
                                        </item>
                                    </template>
                                    <template #body>
                                        <!-- Width -->

                                        <field-style-row
                                            icon="fa fa-arrows-h"
                                            :name="
                                                $t('document.style.frame.width')
                                            "
                                            v-model="width"
                                            @change="updateField"
                                        />

                                        <!-- Height -->

                                        <field-style-row
                                            icon="fa fa-arrows-v"
                                            :name="
                                                $t(
                                                    'document.style.frame.height'
                                                )
                                            "
                                            v-model="height"
                                            @change="updateField"
                                        />

                                        <!-- Padding -->

                                        <tree-layout :tabulation="5">
                                            <template #header>
                                                <field-style-row
                                                    icon="fa fa-expand"
                                                    :name="
                                                        $t(
                                                            'document.style.frame.padding.title'
                                                        )
                                                    "
                                                >
                                                    <input
                                                        v-model="padding"
                                                        placeholder="..."
                                                        @change="updateField"
                                                        @click.stop
                                                    />
                                                    <icon
                                                        class="fa fa-caret-down"
                                                        icon-size="10"
                                                        :size="30"
                                                    />
                                                </field-style-row>
                                            </template>
                                            <template #body>
                                                <div class="hc-flex-row">
                                                    <!-- Padding Left -->

                                                    <field-style-row
                                                        icon="fa fa-arrow-left"
                                                        :name="
                                                            $t(
                                                                'document.style.frame.padding.left'
                                                            )
                                                        "
                                                    >
                                                        <input
                                                            placeholder="..."
                                                            v-model="
                                                                paddingLeft
                                                            "
                                                            @change="
                                                                updateField
                                                            "
                                                        />
                                                    </field-style-row>

                                                    <!-- Padding Right -->

                                                    <field-style-row
                                                        icon="fa fa-arrow-right"
                                                        :name="
                                                            $t(
                                                                'document.style.frame.padding.right'
                                                            )
                                                        "
                                                    >
                                                        <input
                                                            placeholder="..."
                                                            v-model="
                                                                paddingRight
                                                            "
                                                            @change="
                                                                updateField
                                                            "
                                                        />
                                                    </field-style-row>
                                                </div>
                                                <div class="hc-flex-row">
                                                    <!-- Padding Top -->

                                                    <field-style-row
                                                        icon="fa fa-arrow-up"
                                                        :name="
                                                            $t(
                                                                'document.style.frame.padding.top'
                                                            )
                                                        "
                                                    >
                                                        <input
                                                            placeholder="..."
                                                            v-model="paddingTop"
                                                            @change="
                                                                updateField
                                                            "
                                                        />
                                                    </field-style-row>

                                                    <!-- Padding Bottom -->

                                                    <field-style-row
                                                        icon="fa fa-arrow-down"
                                                        :name="
                                                            $t(
                                                                'document.style.frame.padding.bottom'
                                                            )
                                                        "
                                                    >
                                                        <input
                                                            placeholder="..."
                                                            v-model="
                                                                paddingBottom
                                                            "
                                                            @change="
                                                                updateField
                                                            "
                                                        />
                                                    </field-style-row>
                                                </div>
                                            </template>
                                        </tree-layout>

                                        <!-- Background color -->

                                        <field-style-row
                                            icon="fa fa-palette"
                                            :name="
                                                $t(
                                                    'document.style.frame.background_color'
                                                )
                                            "
                                        >
                                            <input
                                                v-model="backgroundColor"
                                                type="color"
                                                @change="updateField"
                                            />
                                        </field-style-row>
                                    </template>
                                </tree-layout>

                                <!-- Text / Format -->

                                <tree-layout
                                    class="hc-document-styles-bloc"
                                    :open="true"
                                >
                                    <template #header>
                                        <item>
                                            <icon
                                                class="fa fa-square"
                                                color="#333333"
                                            />
                                            <div
                                                class="hc-item-main-content hc-document-styles-bloc-title"
                                                v-text="
                                                    $t(
                                                        'document.style.format.title'
                                                    )
                                                "
                                            ></div>
                                            <icon
                                                class="fa fa-caret-down"
                                                :size="30"
                                            />
                                        </item>
                                    </template>
                                    <template #body>
                                        <div
                                            class="hc-flex-column"
                                            style="
                                                align-items: center;
                                                gap: 2px;
                                            "
                                        >
                                            <div
                                                class="hc-flex-row"
                                                style="gap: 2px"
                                            >
                                                <!-- Bold -->

                                                <field-style-button
                                                    icon="fa fa-bold"
                                                    v-model="bold"
                                                    @change="updateField"
                                                />

                                                <!-- Italic -->

                                                <field-style-button
                                                    icon="fa fa-italic"
                                                    v-model="italic"
                                                    @change="updateField"
                                                />

                                                <!-- Underline -->

                                                <field-style-button
                                                    icon="fa fa-underline"
                                                    v-model="underline"
                                                    @change="updateField"
                                                />

                                                <!-- Strikethrough -->

                                                <field-style-button
                                                    icon="fa fa-strikethrough"
                                                    v-model="lineThrough"
                                                    @change="updateField"
                                                />

                                                <!-- Uppercase -->

                                                <field-style-button
                                                    icon="fa fa-text-height"
                                                    v-model="uppercase"
                                                    @change="updateField"
                                                />
                                            </div>
                                            <div
                                                class="hc-flex-row"
                                                style="gap: 2px"
                                            >
                                                <!-- Align center -->

                                                <field-style-button
                                                    icon="fa fa-align-center"
                                                    v-model="textAlignCenter"
                                                    @change="updateField"
                                                />

                                                <!-- Align right -->

                                                <field-style-button
                                                    icon="fa fa-align-right"
                                                    v-model="textAlignRight"
                                                    @change="updateField"
                                                />

                                                <!-- Whitespace ellipsis -->

                                                <field-style-button
                                                    icon="fa fa-ellipsis-h"
                                                    v-model="ellipsis"
                                                    @change="updateField"
                                                />

                                                <!-- Whitespace normal -->

                                                <field-style-button
                                                    icon="fa fa-align-justify"
                                                    v-model="whiteSpaceNormal"
                                                    @change="updateField"
                                                />

                                                <!-- Overflow hidden -->

                                                <field-style-button
                                                    icon="fa fa-square"
                                                    v-model="overflowHidden"
                                                    @change="updateField"
                                                />
                                            </div>
                                        </div>

                                        <!-- Font size -->

                                        <field-style-row
                                            icon="fa fa-text-width"
                                            :name="
                                                $t(
                                                    'document.style.format.font_size'
                                                )
                                            "
                                        >
                                            <select
                                                v-model="fontSize"
                                                @change="updateField"
                                            >
                                                <option
                                                    v-for="i in 65"
                                                    :value="i + 5 + 'px'"
                                                    v-text="i + 5"
                                                ></option>
                                            </select>
                                            <icon
                                                class="fa fa-caret-down"
                                                icon-size="10"
                                                :size="30"
                                            />
                                        </field-style-row>

                                        <!-- LIne height -->

                                        <field-style-row
                                            icon="fa fa-text-height"
                                            :name="
                                                $t(
                                                    'document.style.format.line_height'
                                                )
                                            "
                                            v-model="lineHeight"
                                            @change="updateField"
                                        />
                                        <field-style-row
                                            icon="fa fa-palette"
                                            :name="
                                                $t(
                                                    'document.style.format.color'
                                                )
                                            "
                                            @change="updateField"
                                        >
                                            <input
                                                v-model="color"
                                                type="color"
                                            />
                                        </field-style-row>
                                    </template>
                                </tree-layout>

                                <!-- Border -->

                                <tree-layout :open="true">
                                    <template #header>
                                        <item>
                                            <icon
                                                class="fa fa-expand"
                                                color="#333333"
                                            />
                                            <div
                                                class="hc-item-main-content hc-document-styles-bloc-title"
                                                v-text="
                                                    $t(
                                                        'document.style.border.title'
                                                    )
                                                "
                                            ></div>
                                            <icon class="fa fa-caret-down" />
                                        </item>
                                    </template>
                                    <template #body>
                                        <!-- Border width -->

                                        <tree-layout :tabulation="5">
                                            <template #header>
                                                <field-style-row
                                                    icon="fa fa-grip-lines"
                                                    :name="
                                                        $t(
                                                            'document.style.border.width.title'
                                                        )
                                                    "
                                                >
                                                    <input
                                                        v-model="borderWidth"
                                                        placeholder="..."
                                                        @change="updateField"
                                                        @click.stop
                                                    />
                                                    <icon
                                                        class="fa fa-caret-down"
                                                        icon-size="10"
                                                        :size="30"
                                                    />
                                                </field-style-row>
                                            </template>
                                            <template #body>
                                                <div class="hc-flex-row">
                                                    <!-- Border left width -->

                                                    <field-style-row
                                                        icon="fa fa-arrow-left"
                                                        :name="
                                                            $t(
                                                                'document.style.border.width.left'
                                                            )
                                                        "
                                                    >
                                                        <input
                                                            placeholder="..."
                                                            v-model="
                                                                borderLeftWidth
                                                            "
                                                            @change="
                                                                updateFields
                                                            "
                                                        />
                                                    </field-style-row>

                                                    <!-- Border right width -->

                                                    <field-style-row
                                                        icon="fa fa-arrow-right"
                                                        :name="
                                                            $t(
                                                                'document.style.border.width.right'
                                                            )
                                                        "
                                                    >
                                                        <input
                                                            placeholder="..."
                                                            v-model="
                                                                borderRightWidth
                                                            "
                                                            @change="
                                                                updateFields
                                                            "
                                                        />
                                                    </field-style-row>
                                                </div>
                                                <div class="hc-flex-row">
                                                    <!-- Border top width -->

                                                    <field-style-row
                                                        icon="fa fa-arrow-up"
                                                        :name="
                                                            $t(
                                                                'document.style.border.width.top'
                                                            )
                                                        "
                                                    >
                                                        <input
                                                            placeholder="..."
                                                            v-model="
                                                                borderTopWidth
                                                            "
                                                            @change="
                                                                updateFields
                                                            "
                                                        />
                                                    </field-style-row>

                                                    <!-- Border bottom width -->

                                                    <field-style-row
                                                        icon="fa fa-arrow-down"
                                                        :name="
                                                            $t(
                                                                'document.style.border.width.bottom'
                                                            )
                                                        "
                                                    >
                                                        <input
                                                            placeholder="..."
                                                            v-model="
                                                                borderBottomWidth
                                                            "
                                                            @change="
                                                                updateFields
                                                            "
                                                        />
                                                    </field-style-row>
                                                </div>
                                            </template>
                                        </tree-layout>

                                        <!-- Border style -->

                                        <field-style-row
                                            icon="fa fa-expand"
                                            :name="
                                                $t(
                                                    'document.style.border.style.title'
                                                )
                                            "
                                        >
                                            <select
                                                v-model="borderStyle"
                                                @change="updateField"
                                            >
                                                <option
                                                    value="solid"
                                                    v-text="
                                                        $t(
                                                            'document.style.border.style.solid'
                                                        )
                                                    "
                                                ></option>
                                                <option
                                                    value="dashed"
                                                    v-text="
                                                        $t(
                                                            'document.style.border.style.dashed'
                                                        )
                                                    "
                                                ></option>
                                                <option
                                                    value="dotted"
                                                    v-text="
                                                        $t(
                                                            'document.style.border.style.dotted'
                                                        )
                                                    "
                                                ></option>
                                            </select>
                                            <icon
                                                class="fa fa-caret-down"
                                                :icon-size="10"
                                                :size="30"
                                        /></field-style-row>

                                        <!-- Border color -->

                                        <field-style-row
                                            icon="fa fa-palette"
                                            :name="
                                                $t(
                                                    'document.style.border.color'
                                                )
                                            "
                                            @change="updateField"
                                        >
                                            <input
                                                v-model="borderColor"
                                                type="color"
                                            />
                                        </field-style-row>
                                    </template>
                                </tree-layout>
                            </div>

                            <!-- Apply style to all cell -->

                            <buttons>
                                <button
                                    v-text="
                                        $t(
                                            'document.style.order_table.apply_to_all_cells'
                                        )
                                    "
                                    @click.prevent="applyStyleToOthers"
                                ></button>
                            </buttons>
                        </div>
                    </template>

                    <template #2>
                        <div
                            class="hc-flex-column"
                            style="height: 100%"
                            v-if="column"
                        >
                            <item @click="tab = 1" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="column.header.content"
                                ></div>
                            </item>
                            <item-list class="hc-flex-1" padding="5px">
                                <content-template-row
                                    v-for="(
                                        template, i
                                    ) in columnContentTemplates"
                                    :key="i"
                                    :template="template"
                                    @click.prevent="setColumnTemplate(template)"
                                />
                            </item-list>
                        </div>
                        <div
                            class="hc-flex-column"
                            style="height: 100%"
                            v-else-if="summary"
                        >
                            <item @click="tab = 1" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="summary.header.content"
                                ></div>
                            </item>
                            <item-list class="hc-flex-1" padding="5px">
                                <content-template-row
                                    v-for="(
                                        template, i
                                    ) in summaryContentTemplates"
                                    :key="i"
                                    :template="template"
                                    @click.prevent="
                                        setSummaryTemplate(template)
                                    "
                                />
                            </item-list>
                        </div>
                    </template>
                </frame-layout>
            </template>
        </tab-layout>
    </modal>
</template>

<script>
import store from "@/store";
import { mapGetters } from "vuex";
import cssProperties from "../../cssProperties";

// Components
import ColumnRow from "./ColumnRow.vue";
import SummaryRow from "./SummaryRow.vue";
import ContentTemplateRow from "./ContentTemplateRow.vue";

import FieldStyleRow from "../../components/FieldStyleRow.vue";
import FieldStyleButton from "../../components/FieldStyleButton.vue";

import { UPDATE_DOCUMENT_FIELD } from "@/actions/project/document";

export default {
    components: {
        ColumnRow,
        SummaryRow,
        ContentTemplateRow,
        FieldStyleRow,
        FieldStyleButton,
    },

    data() {
        return {
            name: "document-order-table",
            tab: 0,
            columnTab: 0,
            columnEditTab: 0,
            column: null,
            summary: null,
            selectedField: null,
            content: null,
            columnContentTemplates: [
                {
                    name: "Nom du produit",
                    content: "{product.name}",
                },
                {
                    name: "Description du produit",
                    content: "{product.description}",
                },
                {
                    name: "Index du produit",
                    content: "{product.index}",
                },
                {
                    name: "Quantité du produit",
                    content: "{product.pivot.quantity}",
                },
                {
                    name: "Prix du produit défini dans le devis",
                    content: "{product.pivot.price_value}",
                },
                {
                    name: "Prix HT du produit défini dans le devis",
                    content: "{product.pivot.price_excluding_tax}",
                },
                {
                    name: "Prix TTC du produit défini dans le devis",
                    content: "{product.pivot.price_including_tax}",
                },
                {
                    name: "Taxe du produit défini dans le devis",
                    content: "{product.pivot.tax_value}",
                },
                {
                    name: "Taxe en % du produit défini dans le devis",
                    content: "{product.pivot.tax_percent}",
                },
                {
                    name: "Devise du produit défini dans le devis",
                    content: "{product.pivot.currency}",
                },
                {
                    name: "Prix officiel du produit",
                    content: "{product.price_value}",
                },
                {
                    name: "Prix HT officiel du produit",
                    content: "{product.price_excluding_tax}",
                },
                {
                    name: "Prix TTC officiel du produit",
                    content: "{product.price_including_tax}",
                },
                {
                    name: "Taxe officiel du produit",
                    content: "{product.tax_value}",
                },
                {
                    name: "Taxe en % officiel du produit",
                    content: "{product.tax_percent}",
                },
                {
                    name: "Devise officiel du produit",
                    content: "{product.currency}",
                },
            ],
            summaryContentTemplates: [
                {
                    name: "Total HT",
                    content: "{order.total_excluding_tax} {order.currency}",
                },
                {
                    name: "TVA",
                    content: "{order.tax_value} {order.currency}",
                },
                {
                    name: "Total TTC",
                    content: "{order.total_including_tax} {order.currency}",
                },
            ],
        };
    },

    methods: {
        setColumn(column) {
            this.column = column;
            this.summary = null;
            this.tab = 1;
        },

        setSummary(summary) {
            this.summary = summary;
            this.column = null;
            this.tab = 1;
        },

        updateField() {
            store.dispatch(UPDATE_DOCUMENT_FIELD, {
                id: this.documentField.id,
                content: JSON.stringify(this.content),
            });
        },

        /**
         * Get selected field style
         *
         * @param {*} property css name
         */
        getSelectedFieldStyle(property) {
            const style = cssProperties[property];
            if (!style) {
                return;
            }

            if (!this.selectedField) {
                return;
            }

            let value = null;

            const field = this.selectedField;
            const fieldStyle = field.style[style.name];

            value =
                fieldStyle === undefined
                    ? // default value
                      style.default
                    : style.get
                    ? style.get(fieldStyle)
                    : fieldStyle;

            return value;
        },

        /**
         * Set selected field style
         *
         * @param {*} property css name
         * @param {*} value
         */
        setSelectedFieldStyle(property, value) {
            const style = cssProperties[property];
            if (!style) {
                return;
            }

            if (!this.selectedField) {
                return;
            }

            this.selectedField.style = {
                ...this.selectedField.style,
                [style.name]: style.set
                    ? style.set(value, this.selectedField.style[style.name])
                    : value,
            };
        },

        setContent() {
            if (!this.modalOpen(this.name)) {
                return;
            }

            if (!this.documentField) {
                return null;
            }

            if (this.documentField.type != "order-table") {
                return null;
            }

            this.content = JSON.parse(this.documentField.content);
        },

        setColumnTemplate(template) {
            this.column.body.content = template.content;

            if (!this.column.header.content) {
                this.column.header.content = template.name;
            }

            this.updateField();
            this.tab = 1;
        },

        setSummaryTemplate(template) {
            this.summary.body.content = template.content;

            if (!this.summary.header.content) {
                this.summary.header.content = template.name;
            }

            this.updateField();
            this.tab = 1;
        },

        addColumn() {
            const length = this.content.columns.length;
            const newColumn = {
                header: {
                    content: "",
                    style:
                        length > 0
                            ? this.content.columns[length - 1].header.style
                            : {},
                },
                body: {
                    content: "Contenu ...",
                    style:
                        length > 0
                            ? this.content.columns[length - 1].body.style
                            : {},
                },
            };

            this.content.columns = [...this.content.columns, newColumn];

            return newColumn;
        },

        addSummary() {
            const length = this.content.summaries.length;
            const newSummary = {
                header: {
                    content: "",
                    style:
                        length > 0
                            ? this.content.summaries[length - 1].header.style
                            : {},
                },
                body: {
                    content: "Contenu ...",
                    style:
                        length > 0
                            ? this.content.summaries[length - 1].body.style
                            : {},
                },
            };

            this.content.summaries = [...this.content.summaries, newSummary];

            return newSummary;
        },

        removeColumn() {
            const index = this.content.columns.indexOf(this.column);

            this.content.columns = this.content.columns.filter(
                (c, i) => i != index
            );
            this.updateField();
            this.tab = 0;
        },

        removeSummary() {
            const index = this.content.summaries.indexOf(this.summary);

            this.content.summaries = this.content.summaries.filter(
                (c, i) => i != index
            );
            this.updateField();
            this.tab = 0;
        },

        getApplyStyleToOthers() {
            if (this.column) {
                const headers = this.columns.map((c) => c.header);
                if (headers.indexOf(this.selectedField) >= 0) {
                    return headers;
                }

                const bodies = this.columns.map((c) => c.body);
                if (bodies.indexOf(this.selectedField) >= 0) {
                    return bodies;
                }
            } else if (this.summary) {
                const headers = this.summaries.map((c) => c.header);
                if (headers.indexOf(this.selectedField) >= 0) {
                    return headers;
                }

                const bodies = this.summaries.map((c) => c.body);
                if (bodies.indexOf(this.selectedField) >= 0) {
                    return bodies;
                }
            }

            return [];
        },

        applyStyleToOthers() {
            this.getApplyStyleToOthers().forEach((h) => {
                h.style = { ...this.selectedField.style };
            });

            this.updateField();
        },
    },

    computed: {
        ...mapGetters(["documentField", "modalOpen"]),

        ...Object.fromEntries(
            Object.keys(cssProperties).map((key) => [
                key,
                {
                    // Get selected field CSS value
                    get() {
                        return this.getSelectedFieldStyle(key);
                    },
                    // Set selected field CSS value
                    set(value) {
                        this.setSelectedFieldStyle(key, value);
                    },
                },
            ])
        ),

        summaries() {
            if (!this.content) {
                return [];
            }

            if (!this.content.summaries) {
                return [];
            }

            return this.content.summaries;
        },

        columns() {
            if (!this.content) {
                return [];
            }

            if (!this.content.columns) {
                return [];
            }

            return this.content.columns;
        },
    },
};
</script>
