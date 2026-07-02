<template>
    <div id="hc-document">
        <div
            v-if="mobile"
            id="hc-document-mobile"
            v-text="$t('document.mobile')"
        ></div>
        <div
            id="hc-document-edit"
            v-else-if="document"
            @dragenter.prevent
            @dragover.prevent
            @dragleave.prevent
            @drop.prevent="uploadDroppedFiles"
        >
            <!-- List of pages -->
            <draggable
                id="hc-document-pages"
                :list="documentPages"
                item-key="id"
                @end="pageMoved"
            >
                <template #header>
                    <item
                        style="
                            padding: 0px 0px 0px 5px;
                            gap: 0px;
                            border: 1px solid rgb(204, 204, 204);
                            margin-bottom: 5px;
                            position: sticky;
                            top: 0;
                            z-index: 1;
                            background-color: white;
                        "
                        @click="addPage"
                    >
                        <div
                            class="hc-item-main-content"
                            v-text="$t('document.page.title')"
                        ></div>
                        <icon class="fa fa-plus" :size="26" />
                    </item>
                </template>
                <template #item="{ element }">
                    <page :page="element" />
                </template>
            </draggable>

            <!-- List of field styles settings -->
            <div id="hc-document-styles">
                <!-- Edit order table -->
                <buttons
                    v-if="documentField && documentField.type == 'order-table'"
                >
                    <a
                        @click.prevent="editOrderTable"
                        v-text="$t('document.field.order.table.edit')"
                    ></a>
                </buttons>

                <!-- Zoom -->
                <field-style-row
                    class="hc-document-styles-bloc"
                    icon="hc-icon fa fa-eye"
                    :name="$t('document.zoom.title')"
                >
                    <select v-model="zoom">
                        <option
                            v-for="zoom in zooms"
                            v-text="parseInt(zoom * 100) + '%'"
                            :value="zoom"
                        ></option>
                    </select>
                    <icon class="fa fa-caret-down" icon-size="10" :size="30" />
                </field-style-row>

                <!-- Styles and content -->
                <template v-if="selectedFields.length > 0">
                    <!-- Page -->
                    <field-style-row
                        class="hc-document-styles-bloc"
                        icon="hc-icon fa fa-sort-numeric-up"
                        :name="$t('document.page.title')"
                    >
                        <select v-model="fieldsPage">
                            <option
                                :value="0"
                                v-text="$t('document.page.all')"
                            ></option>
                            <option
                                v-for="documentPage in documentPages"
                                v-text="documentPage.page"
                                :value="documentPage.id"
                            ></option>
                        </select>
                        <icon
                            class="fa fa-caret-down"
                            icon-size="10"
                            :size="30"
                        />
                    </field-style-row>

                    <!-- Content -->
                    <tree-layout class="hc-document-styles-bloc" :open="true">
                        <template #header>
                            <item>
                                <icon class="fa fa-expand" color="#333333" />
                                <div
                                    class="hc-item-main-content hc-document-styles-bloc-title"
                                    v-text="$t('document.style.content.title')"
                                ></div>
                                <icon class="fa fa-caret-down" />
                            </item>
                        </template>
                        <template #body>
                            <textarea
                                v-if="
                                    fieldType == 'qrcode' || fieldType == 'text'
                                "
                                v-model="documentFieldContent"
                                @change="updateFields"
                                style="
                                    width: 100%;
                                    border-radius: 5px;
                                    border: 1px solid #ccc;
                                "
                            ></textarea>
                            <text-editor
                                v-else-if="fieldType == 'html'"
                                v-model.lazy="documentFieldContent"
                                @change="updateFields"
                                style="
                                    width: 100%;
                                    border-radius: 5px;
                                    border: 1px solid #ccc;
                                "
                            />
                        </template>
                    </tree-layout>

                    <!-- Format -->
                    <tree-layout
                        class="hc-document-styles-bloc"
                        :open="true"
                        v-if="
                            ['text', 'html', 'qrcode', 'order-table'].indexOf(
                                fieldType
                            ) >= 0
                        "
                    >
                        <template #header>
                            <item>
                                <icon class="fa fa-font" color="#333333" />
                                <div
                                    class="hc-item-main-content hc-document-styles-bloc-title"
                                    v-text="$t('document.style.format.title')"
                                ></div>
                                <icon class="fa fa-caret-down" :size="30" />
                            </item>
                        </template>
                        <template #body>
                            <template
                                v-if="
                                    fieldType == 'qrcode' ||
                                    fieldType == 'text' ||
                                    fieldType == 'html'
                                "
                            >
                                <div class="hc-flex-row">
                                    <field-type
                                        text="Qrcode"
                                        v-model="fieldTypeqrcode"
                                        @change="updateFields"
                                    >
                                        <i class="fa fa-qrcode"></i>
                                    </field-type>
                                    <field-type
                                        text="Text"
                                        v-model="fieldTypetext"
                                        @change="updateFields"
                                    >
                                        <i class="fa fa-font"></i>
                                    </field-type>
                                    <field-type
                                        text="HTML"
                                        v-model="fieldTypehtml"
                                        @change="updateFields"
                                    >
                                        <i class="fa fa-code"></i>
                                    </field-type>
                                </div>
                            </template>

                            <template
                                v-if="
                                    fieldType != 'qrcode' &&
                                    fieldType != 'image' &&
                                    fieldType != 'map'
                                "
                            >
                                <div
                                    class="hc-flex-column"
                                    style="align-items: center; gap: 2px"
                                >
                                    <div class="hc-flex-row" style="gap: 2px">
                                        <field-style-button
                                            icon="fa fa-bold"
                                            v-model="bold"
                                            @change="updateFields"
                                        />
                                        <field-style-button
                                            icon="fa fa-italic"
                                            v-model="italic"
                                            @change="updateFields"
                                        />
                                        <field-style-button
                                            icon="fa fa-underline"
                                            v-model="underline"
                                            @change="updateFields"
                                        />
                                        <field-style-button
                                            icon="fa fa-strikethrough"
                                            v-model="lineThrough"
                                            @change="updateFields"
                                        />
                                        <field-style-button
                                            icon="fa fa-text-height"
                                            v-model="uppercase"
                                            @change="updateFields"
                                        />
                                    </div>
                                    <div class="hc-flex-row" style="gap: 2px">
                                        <field-style-button
                                            icon="fa fa-align-center"
                                            v-model="textAlignCenter"
                                            @change="updateFields"
                                        />
                                        <field-style-button
                                            icon="fa fa-align-right"
                                            v-model="textAlignRight"
                                            @change="updateFields"
                                        />
                                        <field-style-button
                                            icon="fa fa-ellipsis-h"
                                            v-model="ellipsis"
                                            @change="updateFields"
                                        />
                                        <field-style-button
                                            icon="fa fa-align-justify"
                                            v-model="whiteSpaceNormal"
                                            @change="updateFields"
                                        />
                                        <field-style-button
                                            icon="fa fa-square"
                                            v-model="overflowHidden"
                                            @change="updateFields"
                                        />
                                    </div>
                                </div>
                                <field-style-row
                                    icon="fa fa-text-width"
                                    :name="
                                        $t('document.style.format.font_size')
                                    "
                                >
                                    <select
                                        v-model="fontSize"
                                        @change="updateFields"
                                    >
                                        <option
                                            v-for="i in 65"
                                            :value="i + 5 + 'px'"
                                            v-text="i + 5"
                                        ></option>
                                    </select>
                                </field-style-row>
                                <field-style-row
                                    icon="fa fa-text-height"
                                    :name="
                                        $t('document.style.format.line_height')
                                    "
                                    v-model="lineHeight"
                                    @change="updateFields"
                                />
                                <field-style-row
                                    icon="fa fa-font"
                                    name="Police"
                                    id="hc-document-style-text-font"
                                >
                                    <select
                                        v-model="fontFamily"
                                        @change="updateFields"
                                    >
                                        <option value="monospace">
                                            Monospace
                                        </option>
                                        <option value="sans-serif">
                                            Sans-serif
                                        </option>
                                        <option value="serif">Serif</option>
                                        <option
                                            v-for="font in fonts"
                                            :value="font"
                                            v-text="font"
                                        ></option>
                                    </select>
                                </field-style-row>
                                <field-style-row
                                    icon="fa fa-palette"
                                    :name="$t('document.style.format.color')"
                                    @change="updateFields"
                                >
                                    <input v-model="color" type="color" />
                                </field-style-row>
                            </template>
                        </template>
                    </tree-layout>

                    <!-- Frame -->
                    <tree-layout class="hc-document-styles-bloc" :open="true">
                        <template #header>
                            <item>
                                <icon class="fa fa-square" color="#333333" />
                                <div
                                    class="hc-item-main-content hc-document-styles-bloc-title"
                                    v-text="$t('document.style.frame.title')"
                                ></div>
                                <icon class="fa fa-caret-down" />
                            </item>
                        </template>
                        <template #body>
                            <field-style-row
                                icon="fa fa-arrow-right"
                                :name="$t('document.style.frame.left')"
                                v-model="left"
                                @change="updateFields"
                            />
                            <field-style-row
                                icon="fa fa-arrow-down"
                                :name="$t('document.style.frame.top')"
                                v-model="top"
                                @change="updateFields"
                            />
                            <field-style-row
                                icon="fa fa-arrows-h"
                                :name="$t('document.style.frame.width')"
                                v-model="width"
                                @change="updateFields"
                            />
                            <field-style-row
                                icon="fa fa-arrows-v"
                                :name="$t('document.style.frame.height')"
                                v-model="height"
                                @change="updateFields"
                            />
                            <field-style-row
                                icon="fa fa-undo"
                                :name="$t('document.style.frame.rotation')"
                            >
                                <input v-model.lazy="rotation" type="number" />
                                <div class="hc-document-style-unit">deg</div>
                            </field-style-row>
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
                                            @change="updateFields"
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
                                                v-model="paddingLeft"
                                                @change="updateFields"
                                            />
                                        </field-style-row>
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
                                                v-model="paddingRight"
                                                @change="updateFields"
                                            />
                                        </field-style-row>
                                    </div>
                                    <div class="hc-flex-row">
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
                                                @change="updateFields"
                                            />
                                        </field-style-row>
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
                                                v-model="paddingBottom"
                                                @change="updateFields"
                                            />
                                        </field-style-row>
                                    </div>
                                </template>
                            </tree-layout>
                            <field-style-row
                                icon="fa fa-palette"
                                :name="
                                    $t('document.style.frame.background_color')
                                "
                            >
                                <input
                                    v-model="backgroundColor"
                                    type="color"
                                    @change="updateFields"
                                />
                            </field-style-row>
                        </template>
                    </tree-layout>

                    <!-- Border -->
                    <tree-layout class="hc-document-styles-bloc" :open="true">
                        <template #header>
                            <item>
                                <icon class="fa fa-expand" color="#333333" />
                                <div
                                    class="hc-item-main-content hc-document-styles-bloc-title"
                                    v-text="$t('document.style.border.title')"
                                ></div>
                                <icon class="fa fa-caret-down" />
                            </item>
                        </template>
                        <template #body>
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
                                            @change="updateFields"
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
                                                v-model="borderLeftWidth"
                                                @change="updateFields"
                                            />
                                        </field-style-row>
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
                                                v-model="borderRightWidth"
                                                @change="updateFields"
                                            />
                                        </field-style-row>
                                    </div>
                                    <div class="hc-flex-row">
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
                                                v-model="borderTopWidth"
                                                @change="updateFields"
                                            />
                                        </field-style-row>
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
                                                v-model="borderBottomWidth"
                                                @change="updateFields"
                                            />
                                        </field-style-row>
                                    </div>
                                </template>
                            </tree-layout>

                            <div class="hc-flex-row">
                                <field-type
                                    :text="
                                        $t('document.style.border.style.solid')
                                    "
                                    v-model="borderStylesolid"
                                    @change="updateFields"
                                >
                                    <svg
                                        version="1.1"
                                        xmlns="http://www.w3.org/2000/svg"
                                        xmlns:xlink="http://www.w3.org/1999/xlink"
                                        x="0px"
                                        y="0px"
                                        viewBox="0 0 50 50"
                                        style="enable-background: new 0 0 50 50"
                                        xml:space="preserve"
                                    >
                                        <path
                                            d="M40.5,40.5h-31v-31h31V40.5z M10.5,39.5h29v-29h-29V39.5z"
                                        />
                                    </svg>
                                </field-type>
                                <field-type
                                    :text="
                                        $t('document.style.border.style.dotted')
                                    "
                                    v-model="borderStyledotted"
                                    @change="updateFields"
                                >
                                    <svg
                                        version="1.1"
                                        xmlns="http://www.w3.org/2000/svg"
                                        xmlns:xlink="http://www.w3.org/1999/xlink"
                                        x="0px"
                                        y="0px"
                                        viewBox="0 0 50 50"
                                        style="enable-background: new 0 0 50 50"
                                        xml:space="preserve"
                                    >
                                        <circle cx="40" cy="39.9" r="0.9" />
                                        <circle cx="40" cy="36.1" r="0.9" />
                                        <circle cx="40" cy="32.4" r="0.9" />
                                        <circle cx="40" cy="24.9" r="0.9" />
                                        <circle cx="40" cy="21.2" r="0.9" />
                                        <circle cx="40" cy="17.4" r="0.9" />
                                        <circle cx="40" cy="13.7" r="0.9" />
                                        <circle cx="39.9" cy="10" r="0.9" />
                                        <circle cx="36.2" cy="10" r="0.9" />
                                        <circle cx="32.4" cy="10" r="0.9" />
                                        <circle cx="28.7" cy="10" r="0.9" />
                                        <circle cx="24.9" cy="10" r="0.9" />
                                        <circle cx="21.2" cy="10" r="0.9" />
                                        <circle cx="17.4" cy="10" r="0.9" />
                                        <circle cx="13.7" cy="10" r="0.9" />
                                        <circle cx="10" cy="10.1" r="0.9" />
                                        <circle cx="10" cy="13.8" r="0.9" />
                                        <circle cx="10" cy="17.5" r="0.9" />
                                        <circle cx="10" cy="21.3" r="0.9" />
                                        <circle cx="10" cy="25" r="0.9" />
                                        <circle cx="10" cy="28.8" r="0.9" />
                                        <circle cx="10" cy="32.5" r="0.9" />
                                        <circle cx="10" cy="36.3" r="0.9" />
                                        <circle cx="10" cy="40" r="0.9" />
                                        <circle cx="13.8" cy="40" r="0.9" />
                                        <circle cx="17.5" cy="40" r="0.9" />
                                        <circle cx="21.3" cy="40" r="0.9" />
                                        <circle cx="25" cy="40" r="0.9" />
                                        <circle cx="28.8" cy="40" r="0.9" />
                                        <circle cx="32.5" cy="40" r="0.9" />
                                        <circle cx="36.3" cy="40" r="0.9" />
                                        <circle cx="40" cy="40" r="0.9" />
                                    </svg>
                                </field-type>
                                <field-type
                                    :text="
                                        $t('document.style.border.style.dashed')
                                    "
                                    v-model="borderStyledashed"
                                    @change="updateFields"
                                >
                                    <svg
                                        version="1.1"
                                        xmlns="http://www.w3.org/2000/svg"
                                        xmlns:xlink="http://www.w3.org/1999/xlink"
                                        x="0px"
                                        y="0px"
                                        viewBox="0 0 50 50"
                                        style="enable-background: new 0 0 50 50"
                                        xml:space="preserve"
                                    >
                                        <rect
                                            x="9.2"
                                            y="21"
                                            width="1.7"
                                            height="8"
                                        />
                                        <rect
                                            x="21"
                                            y="9.2"
                                            width="8"
                                            height="1.7"
                                        />
                                        <rect
                                            x="39.2"
                                            y="21"
                                            width="1.7"
                                            height="8"
                                        />
                                        <rect
                                            x="21"
                                            y="39.2"
                                            width="8"
                                            height="1.7"
                                        />
                                        <polygon
                                            points="10.8,13 10.8,10.8 9.2,9.2 9.2,13"
                                        />
                                        <polygon
                                            points="13,10.8 10.8,10.8 9.2,9.2 13,9.2"
                                        />
                                        <polygon
                                            points="37,10.8 39.2,10.8 40.8,9.2 37,9.2"
                                        />
                                        <polygon
                                            points="39.2,13 39.2,10.8 40.8,9.2 40.8,13"
                                        />
                                        <polygon
                                            points="39.2,37 39.2,39.2 40.8,40.8 40.8,37"
                                        />
                                        <polygon
                                            points="37,39.2 39.2,39.2 40.8,40.8 37,40.8"
                                        />
                                        <polygon
                                            points="13,39.2 10.8,39.2 9.2,40.8 13,40.8"
                                        />
                                        <polygon
                                            points="10.8,37 10.8,39.2 9.2,40.8 9.2,37"
                                        />
                                    </svg>
                                </field-type>
                            </div>

                            <field-style-row
                                icon="fa fa-palette"
                                :name="$t('document.style.border.color')"
                                :disabled="!selectedFieldsHasBorder"
                                @change="updateFields"
                            >
                                <input v-model="borderColor" type="color" />
                            </field-style-row>
                            <field-style-row
                                icon="fa fa-circle"
                                :name="$t('document.style.border.radius')"
                                :disabled="!selectedFieldsHasBorder"
                                v-model="borderRadius"
                                @change="updateFields"
                            />
                        </template>
                    </tree-layout>

                    <!-- Map -->
                    <tree-layout
                        class="hc-document-styles-bloc"
                        :open="true"
                        v-if="fieldType == 'map'"
                    >
                        <template #header>
                            <item>
                                <icon
                                    class="fa fa-map-marker"
                                    color="#333333"
                                />
                                <div
                                    class="hc-item-main-content hc-document-styles-bloc-title"
                                    v-text="$t('document.map.title')"
                                ></div>
                                <icon class="fa fa-caret-down" :size="30" />
                            </item>
                        </template>
                        <template #body>
                            <field-style-row icon="fa fa-image" name="Type">
                                <select
                                    v-model="maptype"
                                    @change="updateFields"
                                >
                                    <option
                                        value="satellite"
                                        v-text="$t('document.map.satellite')"
                                    ></option>
                                    <option
                                        value="roadmap"
                                        v-text="$t('document.map.roadmap')"
                                    ></option>
                                    <option
                                        value="hybrid"
                                        v-text="$t('document.map.hybrid')"
                                    ></option>
                                    <option
                                        value="terrain"
                                        v-text="$t('document.map.terrain')"
                                    ></option>
                                </select>
                                <icon
                                    class="fa fa-caret-down"
                                    icon-size="10"
                                    :size="30"
                                />
                            </field-style-row>
                            <field-style-row
                                icon="fa fa-search"
                                :name="$t('document.map.zoom')"
                            >
                                <select
                                    v-model="mapzoom"
                                    @change="updateFields"
                                >
                                    <option
                                        v-for="i in 11"
                                        :value="i + 9"
                                        v-text="i + 9"
                                    ></option>
                                </select>
                                <icon
                                    class="fa fa-caret-down"
                                    icon-size="10"
                                    :size="30"
                                />
                            </field-style-row>
                        </template>
                    </tree-layout>
                </template>
            </div>

            <!-- PDF and fields Viewer -->
            <div
                id="hc-document-view-wrapper"
                @dragenter.prevent
                @dragover.prevent
                @dragleave.prevent
                @click.stop="selectedFields = []"
                @dblclick.stop="placeNewField"
            >
                <div
                    id="hc-document-view"
                    v-if="documentPage"
                    @dragenter.prevent
                    @dragover.prevent
                    @dragleave.prevent
                    @drop.prevent="uploadDroppedFields"
                    :style="{
                        width: documentPage.width,
                        height: documentPage.height,
                        transform: 'scale(' + documentZoom + ')',
                    }"
                    ref="pdf"
                >
                    <VuePDF
                        v-if="pdf"
                        :pdf="pdf"
                        :page="pagePDFPage"
                        :scale="scale"
                        id="pdf-view"
                        style="position: absolute !important"
                    />

                    <field
                        v-for="field in shownFields"
                        :key="field.id"
                        :field="field"
                        :renderers="renderers"
                        :selected="isFieldSelected(field)"
                        @selected="setSelectedField"
                    />
                </div>
            </div>

            <frame-layout
                :count="10"
                :tab="documentTemplateTab"
                id="hc-document-templates"
            >
                <!-- Pages -->

                <template #1>
                    <div class="hc-document-templates">
                        <div class="hc-document-templates-title">
                            <span
                                v-text="$t('document.field.tabs.pages.title')"
                            ></span>
                            <a
                                class="hc-document-templates-add fa fa-plus"
                                @click.prevent="addPage"
                            ></a>
                        </div>
                        <draggable
                            class="hc-document-templates-list"
                            :list="documentPages"
                            item-key="id"
                            @end="pageMoved"
                        >
                            <template #item="{ element }">
                                <page :page="element" />
                            </template>
                            <template #footer>
                                <field-template
                                    type="html"
                                    content="{page}"
                                    @dragging="dragNewField"
                                    @dragged="dropNewField"
                                >
                                    <div class="hc-document-page">
                                        <div
                                            class="hc-document-page-container"
                                            style="
                                                background-color: #3598d8;
                                                color: white;
                                                font-weight: 500;
                                            "
                                        >
                                            <div
                                                class="hc-document-page-content"
                                            >
                                                <span
                                                    v-text="
                                                        $t(
                                                            'document.field.tabs.pages.number'
                                                        )
                                                    "
                                                ></span>
                                            </div>
                                        </div>
                                    </div>
                                </field-template>
                                <div
                                    class="hc-document-page"
                                    @click="addA4PortraitPage"
                                >
                                    <div class="hc-document-page-container">
                                        <div class="hc-document-page-content">
                                            <span
                                                >A4<br />{{
                                                    $t(
                                                        "document.field.tabs.pages.portrait"
                                                    )
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="hc-document-page"
                                    @click="addA4LandscapePortraitPage"
                                >
                                    <div class="hc-document-page-container">
                                        <div class="hc-document-page-content">
                                            <span
                                                >A4<br />{{
                                                    $t(
                                                        "document.field.tabs.pages.landscape"
                                                    )
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                </div>
                                <div
                                    v-for="file in documentFiles"
                                    :key="file.id"
                                    class="hc-document-page"
                                    @click="addFromFilePage(file)"
                                >
                                    <div class="hc-document-page-container">
                                        <div class="hc-document-page-content">
                                            <img :src="fileThumbnail(file)" />
                                            <span
                                                >{{
                                                    $t(
                                                        "document.field.tabs.pages.new_from"
                                                    )
                                                }}<br /><span
                                                    v-text="
                                                        file.name.substring(
                                                            0,
                                                            20
                                                        )
                                                    "
                                                ></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </draggable>
                    </div>
                </template>

                <!-- Files -->

                <template #2>
                    <div class="hc-document-templates">
                        <div class="hc-document-templates-title">
                            <span
                                v-text="$t('document.field.tabs.pdfs.title')"
                            ></span>
                            <label class="hc-document-templates-add fa fa-plus">
                                <input
                                    type="file"
                                    accept="application/pdf"
                                    @change="uploadSelectedFiles"
                                    multiple
                            /></label>
                        </div>
                        <input
                            class="hc-document-templates-search"
                            :placeholder="$t('search') + ' ...'"
                            v-model="fileKeyword"
                        />
                        <div class="hc-document-templates-list">
                            <file-row
                                v-for="file in filteredFiles"
                                :key="file.id"
                                :file="file"
                            />
                        </div>
                    </div>
                </template>

                <!-- Fonts -->

                <template #3>
                    <div class="hc-document-templates">
                        <div class="hc-document-templates-title">
                            <span
                                v-text="$t('document.field.tabs.fonts.title')"
                            ></span>
                            <a
                                class="hc-document-templates-add fa fa-plus"
                                @click.prevent="addFont"
                            ></a>
                        </div>
                        <input
                            class="hc-document-templates-search"
                            :placeholder="$t('search') + ' ...'"
                            v-model="fontKeyword"
                        />
                        <div class="hc-document-templates-list">
                            <font-row
                                v-for="font in filteredFonts"
                                :key="font.id"
                                :font="font"
                                @click="
                                    (fontFamily = font.name),
                                        (fontStyle = font.style),
                                        (fontWeight = font.weight)
                                "
                            />
                        </div>
                    </div>
                </template>

                <!-- Prospects -->

                <template #4>
                    <div class="hc-document-templates">
                        <div class="hc-document-templates-title">
                            <span
                                v-text="
                                    $t('document.field.tabs.prospects.title')
                                "
                            ></span>
                            <a
                                class="hc-document-templates-add fa fa-plus"
                                @click.prevent="createProspectField"
                            ></a>
                        </div>
                        <input
                            class="hc-document-templates-search"
                            :placeholder="$t('search') + ' ...'"
                            v-model="prospectFieldKeyword"
                        />
                        <div class="hc-document-templates-list">
                            <prospect-default-field-row
                                v-for="field in documentDefaultProspectFields"
                                :key="field.id"
                                :field="field"
                                @dragging="dragNewField"
                                @dragged="dropNewField"
                            />
                            <prospect-meta-field-row
                                v-for="field in documentMetaProspectFields"
                                :key="field.id"
                                :field="field"
                                @dragging="dragNewField"
                                @dragged="dropNewField"
                            />
                        </div>
                    </div>
                </template>

                <!-- Project -->

                <template #5>
                    <div class="hc-document-templates">
                        <div class="hc-document-templates-title">
                            <span
                                v-text="$t('document.field.tabs.project.title')"
                            ></span>
                        </div>
                        <input
                            class="hc-document-templates-search"
                            :placeholder="$t('search') + ' ...'"
                            v-model="projectFieldKeyword"
                        />
                        <div class="hc-document-templates-list">
                            <field-template
                                type="html"
                                :content="'{project.logo}'"
                                @dragging="dragNewField"
                                @dragged="dropNewField"
                            >
                                <item class="hc-document-default-field">
                                    <icon>
                                        <img
                                            :src="thumbnail"
                                            :alt="project.name"
                                        />
                                    </icon>
                                    <div
                                        class="hc-item-main-content"
                                        v-text="
                                            $t(
                                                'document.field.project.thumbnail'
                                            )
                                        "
                                    ></div>
                                    <icon
                                        class="fa fa-arrows"
                                        icon-size="11"
                                        :size="30"
                                        color="#CCCCCC"
                                    />
                                </item>
                            </field-template>
                            <project-default-field-row
                                v-for="field in documentDefaultProjectFields"
                                :key="field.id"
                                :field="field"
                                @dragging="dragNewField"
                                @dragged="dropNewField"
                            />
                            <project-meta-field-row
                                v-for="field in documentMetaProjectFields"
                                :key="field.id"
                                :field="field"
                                @dragging="dragNewField"
                                @dragged="dropNewField"
                            />
                        </div>
                    </div>
                </template>

                <!-- MAP -->

                <template #6>
                    <div class="hc-document-templates">
                        <div class="hc-document-templates-title">
                            <span
                                v-text="$t('document.field.tabs.map.title')"
                            ></span>
                        </div>
                        <div class="hc-document-templates-list">
                            <map-row
                                :map="{
                                    content: { zoom: 18, type: 'satellite' },
                                    image: 'satellite',
                                }"
                                @dragging="dragNewField"
                                @dragged="dropNewField"
                            />
                            <map-row
                                :map="{
                                    content: { zoom: 18, type: 'plan' },
                                    image: 'plan',
                                }"
                                @dragging="dragNewField"
                                @dragged="dropNewField"
                            />
                        </div>
                    </div>
                </template>

                <!-- Date -->

                <template #7>
                    <div class="hc-document-templates">
                        <div class="hc-document-templates-title">
                            <span
                                v-text="$t('document.field.tabs.dates.title')"
                            ></span>
                        </div>
                        <div class="hc-document-templates-list">
                            <date-row
                                v-for="dateFormat in dateFormats"
                                :key="dateFormat"
                                :date="dateFormat"
                                @dragging="dragNewField"
                                @dragged="dropNewField"
                            />
                        </div>
                    </div>
                </template>

                <!-- Order -->

                <template
                    #8
                    v-if="document.for == 'order' || document.for == 'invoice'"
                >
                    <div class="hc-document-templates">
                        <div class="hc-document-templates-title">
                            <span
                                v-text="$t('document.field.tabs.orders.title')"
                            ></span>
                        </div>
                        <div class="hc-document-templates-list">
                            <!-- Order Table -->

                            <field-template
                                :type="fakeOrderField.type"
                                :content="fakeOrderField.content"
                                @dragging="dragNewField"
                                @dragged="dropNewField"
                            >
                                <item class="hc-document-default-field">
                                    <icon
                                        class="fa fa-columns"
                                        icon-size="11"
                                        :size="30"
                                    />
                                    <div
                                        class="hc-item-main-content"
                                        v-text="
                                            $t(
                                                'document.field.order.table.title'
                                            )
                                        "
                                    ></div>
                                    <icon
                                        class="fa fa-arrows"
                                        icon-size="11"
                                        :size="30"
                                        color="#CCCCCC"
                                    />
                                </item>
                            </field-template>

                            <!-- Invoice number -->

                            <field-template
                                :type="'text'"
                                :content="
                                    $t('document.field.order.invoice_number', {
                                        number: '{invoice.id}',
                                    })
                                "
                                @dragging="dragNewField"
                                @dragged="dropNewField"
                            >
                                <item class="hc-document-default-field">
                                    <icon
                                        class="fa fa-file-invoice"
                                        icon-size="11"
                                        :size="30"
                                    />
                                    <div
                                        class="hc-item-main-content"
                                        v-text="
                                            $t('document.field.order.number')
                                        "
                                    ></div>
                                    <icon
                                        class="fa fa-arrows"
                                        icon-size="11"
                                        :size="30"
                                        color="#CCCCCC"
                                    />
                                </item>
                            </field-template>

                            <!-- Invoice date -->

                            <field-template
                                :type="'text'"
                                :content="
                                    $t('document.field.order.invoice_date', {
                                        date: '{invoice.date}',
                                    })
                                "
                                @dragging="dragNewField"
                                @dragged="dropNewField"
                            >
                                <item class="hc-document-default-field">
                                    <icon
                                        class="fa fa-calendar"
                                        icon-size="11"
                                        :size="30"
                                    />
                                    <div
                                        class="hc-item-main-content"
                                        v-text="$t('document.field.order.date')"
                                    ></div>
                                    <icon
                                        class="fa fa-arrows"
                                        icon-size="11"
                                        :size="30"
                                        color="#CCCCCC"
                                    />
                                </item>
                            </field-template>

                            <!-- Order fields -->

                            <order-default-field-row
                                v-for="field in documentDefaultOrderFields"
                                :key="field.id"
                                :field="field"
                                @dragging="dragNewField"
                                @dragged="dropNewField"
                            />

                            <!-- Products fields -->

                            <tree-layout
                                class="hc-document-styles-bloc"
                                :open="false"
                                v-for="i in 4"
                            >
                                <template #header>
                                    <item>
                                        <icon class="fa fa-gift" />
                                        <div
                                            class="hc-item-main-content"
                                            v-text="
                                                $t(
                                                    'document.field.order.product_i',
                                                    { i }
                                                )
                                            "
                                        ></div>
                                        <icon class="fa fa-caret-down" />
                                    </item>
                                </template>
                                <template #body>
                                    <product-default-field-row
                                        v-for="field in documentDefaultProductFields"
                                        :key="field.id"
                                        :field="field"
                                        :i="i - 1"
                                        @dragging="dragNewField"
                                        @dragged="dropNewField"
                                    />
                                </template>
                            </tree-layout>
                        </div>
                    </div>
                </template>

                <!-- Labels -->

                <template #9>
                    <div class="hc-document-templates">
                        <div class="hc-document-templates-title">
                            <span
                                v-text="$t('document.field.tabs.labels.title')"
                            ></span>
                            <a
                                class="hc-document-templates-add fa fa-plus"
                                @click.prevent="createCategory"
                            ></a>
                        </div>
                        <input
                            class="hc-document-templates-search"
                            :placeholder="$t('search') + ' ...'"
                            v-model="categoryKeyword"
                        />
                        <div class="hc-document-templates-list">
                            <category-row
                                v-for="category in filteredCategories"
                                :key="category.id"
                                :category="category"
                                @dragging="dragNewField"
                                @dragged="dropNewField"
                            />
                        </div>
                    </div>
                </template>

                <!-- Questionnaires -->

                <template #10>
                    <div class="hc-document-templates">
                        <div class="hc-document-templates-title">
                            <span
                                v-text="$t('document.field.tabs.forms.title')"
                            ></span>
                            <a
                                class="hc-document-templates-add fa fa-plus"
                                @click.prevent="createQuestionnaire"
                            ></a>
                        </div>
                        <input
                            class="hc-document-templates-search"
                            :placeholder="$t('search') + ' ...'"
                            v-model="questionnaireKeyword"
                        />
                        <div class="hc-document-templates-list">
                            <questionnaire-row
                                v-for="questionnaire in filteredQuestionnaires"
                                :key="questionnaire.id"
                                :questionnaire="questionnaire"
                                @dragging="dragNewField"
                                @dragged="dropNewField"
                            />
                        </div>
                    </div>
                </template>
            </frame-layout>

            <div id="hc-document-templates-tab">
                <!-- Page -->
                <a
                    :class="[
                        'hc-document-templates-tab',
                        documentTemplateTab == 0 ? 'active' : '',
                    ]"
                    v-tuto="{
                        key: 'project.document.template.tab.page',
                        name: $t(
                            'tutorial.project_document_template_tab_page.name'
                        ),
                        body: $t(
                            'tutorial.project_document_template_tab_page.body'
                        ),
                    }"
                    @click.prevent="documentTemplateTab = 0"
                >
                    <i class="fa fa-file"></i>
                    <span v-text="$t('document.field.tabs.pages.title')"></span>
                </a>
                <!-- File -->
                <a
                    :class="[
                        'hc-document-templates-tab',
                        documentTemplateTab == 1 ? 'active' : '',
                    ]"
                    v-tuto="{
                        key: 'project.document.template.tab.file',
                        name: $t(
                            'tutorial.project_document_template_tab_file.name'
                        ),
                        body: $t(
                            'tutorial.project_document_template_tab_file.body'
                        ),
                    }"
                    @click.prevent="documentTemplateTab = 1"
                >
                    <i class="fa fa-file-pdf" style="color: #d62828"></i>
                    <span v-text="$t('document.field.tabs.pdfs.title')"></span>
                </a>
                <!-- Font -->
                <a
                    :class="[
                        'hc-document-templates-tab',
                        documentTemplateTab == 2 ? 'active' : '',
                    ]"
                    v-tuto="{
                        key: 'project.document.template.tab.font',
                        name: $t(
                            'tutorial.project_document_template_tab_font.name'
                        ),
                        body: $t(
                            'tutorial.project_document_template_tab_font.body'
                        ),
                    }"
                    @click.prevent="documentTemplateTab = 2"
                >
                    <i class="fa fa-font" style="color: #0085ff"></i>
                    <span v-text="$t('document.field.tabs.fonts.title')"></span>
                </a>
                <!-- Prospect -->
                <a
                    :class="[
                        'hc-document-templates-tab',
                        documentTemplateTab == 3 ? 'active' : '',
                    ]"
                    v-tuto="{
                        key: 'project.document.template.tab.prospect',
                        name: $t(
                            'tutorial.project_document_template_tab_prospect.name'
                        ),
                        body:
                            $t(
                                'tutorial.project_document_template_tab_prospect.body.0'
                            ) +
                            '<br>' +
                            $t(
                                'tutorial.project_document_template_tab_prospect.body.1'
                            ) +
                            '<br><img style=&quot;width: 100%;margin-top: 10px 0;border-radius: 5px;&quot; src=&quot;/images/tutorial/document.template.tab.prospects.gif&quot; />',
                    }"
                    @click.prevent="documentTemplateTab = 3"
                >
                    <i class="fa fa-users"></i>
                    <span
                        v-text="$t('document.field.tabs.prospects.title')"
                    ></span>
                </a>
                <!-- Project -->
                <a
                    :class="[
                        'hc-document-templates-tab',
                        documentTemplateTab == 4 ? 'active' : '',
                    ]"
                    v-tuto="{
                        key: 'project.document.template.tab.project',
                        name: $t(
                            'tutorial.project_document_template_tab_project.name'
                        ),
                        body: $t(
                            'tutorial.project_document_template_tab_project.body'
                        ),
                    }"
                    @click.prevent="documentTemplateTab = 4"
                >
                    <img :src="thumbnail" :alt="project.name" />
                    <span
                        v-text="$t('document.field.tabs.project.title')"
                    ></span>
                </a>
                <!-- Map -->
                <a
                    :class="[
                        'hc-document-templates-tab',
                        documentTemplateTab == 5 ? 'active' : '',
                    ]"
                    v-tuto="{
                        key: 'project.document.template.tab.map',
                        name: $t(
                            'tutorial.project_document_template_tab_map.name'
                        ),
                        body: $t(
                            'tutorial.project_document_template_tab_map.body'
                        ),
                    }"
                    @click.prevent="documentTemplateTab = 5"
                >
                    <i class="fa fa-map-marker"></i>
                    <span v-text="$t('document.field.tabs.map.title')"></span>
                </a>
                <!-- Date -->
                <a
                    :class="[
                        'hc-document-templates-tab',
                        documentTemplateTab == 6 ? 'active' : '',
                    ]"
                    v-tuto="{
                        key: 'project.document.template.tab.date',
                        name: $t(
                            'tutorial.project_document_template_tab_date.name'
                        ),
                        body: $t(
                            'tutorial.project_document_template_tab_date.body'
                        ),
                    }"
                    @click.prevent="documentTemplateTab = 6"
                >
                    <i class="fa fa-calendar"></i>
                    <span v-text="$t('document.field.tabs.dates.title')"></span>
                </a>
                <!-- Order -->
                <a
                    v-if="document.for == 'order' || document.for == 'invoice'"
                    :class="[
                        'hc-document-templates-tab',
                        documentTemplateTab == 7 ? 'active' : '',
                    ]"
                    v-tuto="{
                        key: 'project.document.template.tab.order',
                        name: $t(
                            'tutorial.project_document_template_tab_order.name'
                        ),
                        body:
                            $t(
                                'tutorial.project_document_template_tab_order.body.0'
                            ) +
                            '<br><img style=&quot;width: 100%;margin-top: 10px 0;border-radius: 5px;&quot; src=&quot;/images/tutorial/document.template.tab.orders.jpg&quot; />',
                    }"
                    @click.prevent="documentTemplateTab = 7"
                >
                    <i class="fa fa-shopping-cart"></i>
                    <span
                        v-text="$t('document.field.tabs.orders.title')"
                    ></span>
                </a>
                <!-- Label -->
                <a
                    :class="[
                        'hc-document-templates-tab',
                        documentTemplateTab == 8 ? 'active' : '',
                    ]"
                    v-tuto="{
                        key: 'project.document.template.tab.label',
                        name: $t(
                            'tutorial.project_document_template_tab_label.name'
                        ),
                        body:
                            $t(
                                'tutorial.project_document_template_tab_label.body.0'
                            ) +
                            '<br><ul><li>' +
                            $t(
                                'tutorial.project_document_template_tab_label.body.1'
                            ) +
                            '</li><li>' +
                            $t(
                                'tutorial.project_document_template_tab_label.body.2'
                            ) +
                            '</li></ul>',
                    }"
                    @click.prevent="documentTemplateTab = 8"
                >
                    <i class="fa fa-tags"></i>
                    <span
                        v-text="$t('document.field.tabs.labels.title')"
                    ></span>
                </a>
                <!-- Formulaire -->
                <a
                    :class="[
                        'hc-document-templates-tab',
                        documentTemplateTab == 9 ? 'active' : '',
                    ]"
                    @click.prevent="documentTemplateTab = 9"
                >
                    <i class="fa fa-clipboard"></i>
                    <span v-text="$t('document.field.tabs.forms.title')"></span>
                </a>
            </div>

            <!-- New field -->
            <field
                v-if="newField"
                :field="newField"
                :renderers="renderers"
                :selected="true"
                :is-new="true"
                @add-field="addPlacedNewField"
            />

            <add-font-modal />
            <add-page-modal />
            <order-table-modal />
        </div>
        <document-dashboard v-else />
    </div>
</template>

<style>
#hc-document {
    display: flex;
    flex-direction: row;
    width: 100%;
    height: 100%;
    background-color: white;
}

#hc-document-mobile {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100%;
    font-size: 20px;
    padding: 30px;
    text-align: center;
}

#hc-document-edit {
    display: flex;
    flex-direction: row;
    width: 100%;
    height: 100%;
    background-color: white;
}

#hc-document-styles {
    width: 230px;
    height: 100%;
    overflow: auto;
    border-right: 1px solid #dddddd;
    padding: 10px;
}

#hc-document-pages {
    width: 135px;
    height: 100%;
    padding: 7px;
    overflow: auto;
    display: flex;
    flex-direction: column;
    border-right: 1px solid #dddddd;
    position: relative;
}

#hc-document-view-wrapper {
    flex: 1;
    background-color: #fafafb;
    position: relative;
    padding: 30px;
    overflow: auto;
}

#hc-document-view {
    margin: auto;
    border: 1px solid #cccccc;
    user-select: none;
    background-color: white;
    transform-origin: top;
}

#hc-document-view canvas {
    width: 100% !important;
    height: auto !important;
}

#hc-document-templates {
    width: 250px;
    height: 100%;
    overflow: auto;
}

#hc-document-templates-tab {
    width: 90px;
    height: 100%;
    overflow: auto;
    background-color: #f5f5f5;
}

.hc-document-templates-tab {
    width: 100%;
    padding: 12px 15px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    text-align: center;
    gap: 5px;
    cursor: pointer;
}

.hc-document-templates-tab > i {
    color: #69696d;
    font-size: 15px;
}

.hc-document-templates-tab > img {
    width: 50px;
}

.hc-document-templates-tab > span {
    color: #000000;
    font-size: 11px;
    font-weight: 400;
    line-height: 16px;
    width: 100%;
    word-break: break-word;
}

.hc-document-templates-tab.active {
    background-color: white;
}

.hc-document-templates-tab.active > i {
    color: #7939b8;
}

.hc-document-templates-tab.active > span {
    color: #7939b8;
}

.hc-document-templates {
    display: flex;
    flex-direction: column;
    padding: 25px 15px;
    gap: 15px;
    height: 100%;
}

.hc-document-templates-title {
    font-size: 18px;
    font-weight: bold;
    display: flex;
    flex-direction: row;
    align-items: center;
}

.hc-document-templates-title > span {
    flex: 1;
}

.hc-document-templates-add {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #7939b822;
    color: #7939b8;
    text-align: center;
    line-height: 40px;
    text-decoration: none;
    font-size: 18px;
    cursor: pointer;
}

.hc-document-templates-add > input {
    display: none;
}

.hc-document-templates-search {
    background-color: #f5f4f5;
    border: none;
    font-size: 12px;
    height: 34px;
    padding: 10px;
    width: 100%;
    border-radius: 5px;
}

.hc-document-templates-list {
    flex: 1;
    overflow: auto;
}

.hc-document-templates-list .hc-document-page {
    float: left;
    width: 50% !important;
}

.hc-document-templates-list .hc-document-page .hc-document-page-content {
    padding-top: 142% !important;
}

.hc-document-styles-bloc {
    width: 100%;
    border-bottom: 1px solid #e5e5e5;
}

.hc-document-styles-bloc-title {
    color: #000000;
    font-weight: bold;
    font-size: 13px;
}

#hc-document-view:hover .hc-document-field {
    outline: 2px solid #e6b000;
}

.hc-document-field-page {
    color: #7939b8;
}
</style>

<script>
import { API_URL } from "@/apis/common";
import { VuePDF, usePDF } from "@tato30/vue-pdf";

import { fakeOrder, fakeOrderField } from "./fakeData";
import cssProperties from "./cssProperties";

import DocumentDashboard from "./dashboard/Dashboard.vue";

import Field from "./components/Field.vue";
import FieldTemplate from "./components/FieldTemplate.vue";
import Page from "./components/Page.vue";
import FileRow from "./components/FileRow.vue";
import FontRow from "./components/FontRow.vue";
import MapRow from "./components/MapRow.vue";
import FieldStyleRow from "./components/FieldStyleRow.vue";
import FieldStyleButton from "./components/FieldStyleButton.vue";
import FieldType from "./components/FieldType.vue";

import ProspectDefaultFieldRow from "./templates/ProspectDefaultFieldRow.vue";
import ProspectMetaFieldRow from "./templates/ProspectMetaFieldRow.vue";
import ProjectDefaultFieldRow from "./templates/ProjectDefaultFieldRow.vue";
import ProjectMetaFieldRow from "./templates/ProjectMetaFieldRow.vue";
import OrderDefaultFieldRow from "./templates/OrderDefaultFieldRow.vue";
import ProductDefaultFieldRow from "./templates/ProductDefaultFieldRow.vue";
import CategoryRow from "./templates/CategoryRow.vue";
import QuestionnaireRow from "./templates/QuestionnaireRow.vue";
import DateRow from "./templates/DateRow.vue";

// Renderers
import DateRenderer from "./renderers/DateRenderer";
import FieldNameRenderer from "./renderers/FieldNameRenderer";
import ImageRenderer from "./renderers/ImageRenderer";
import MapRenderer from "./renderers/MapRenderer";
import OrderFieldRenderer from "./renderers/OrderFieldRenderer";
import OrderTableRenderer from "./renderers/OrderTableRenderer";
import PageNumberRenderer from "./renderers/PageNumberRenderer";
import ProjectFieldRenderer from "./renderers/ProjectFieldRenderer";
import ProspectCategoryRenderer from "./renderers/ProspectCategoryRenderer";
import ProspectCategoryLabelRenderer from "./renderers/ProspectCategoryLabelRenderer";
import ProspectFieldRenderer from "./renderers/ProspectFieldRenderer";
import InvoiceFieldRenderer from "./renderers/InvoiceFieldRenderer";
import ProspectResponseOptionRenderer from "./renderers/ProspectResponseOptionRenderer";
import ProspectResponseRenderer from "./renderers/ProspectResponseRenderer";
import QrcodeRenderer from "./renderers/QrcodeRenderer";
import TextFieldRenderer from "./renderers/TextFieldRenderer";
import UserFieldRenderer from "./renderers/UserFieldRenderer";

import Autocomplete from "@/components/Autocomplete.vue";

import AddFontModal from "./modals/font/add/Modal.vue";
import AddPageModal from "./modals/page/add/Modal.vue";
import OrderTableModal from "./modals/order-table/Modal.vue";

import { mapGetters } from "vuex";
import store from "@/store";

import { OPEN_MODAL } from "@/actions/modal";
import { OPEN_SLIDE, CLOSE_SLIDE } from "@/actions/slide";
import { SET_DOCUMENT_FIELD } from "@/actions/project/document";

import {
    ADD_DOCUMENT_FILE,
    ADD_DOCUMENT_FIELD,
    ADD_DOCUMENT_FONT,
    FETCH_DOCUMENT_PAGES,
    SHOW_DOCUMENT_PAGE,
    UPDATE_DOCUMENT_PAGE,
    UPDATE_DOCUMENT_FIELD,
    UPDATE_DOCUMENT_ZOOM,
    SET_DOCUMENT_NEW_PAGE,
    SET_DOCUMENT_FIELD_EDIT,
} from "@/actions/project/document";
import { render } from "vue";

export default {
    components: {
        DocumentDashboard,

        Field,
        FieldTemplate,
        Page,
        FileRow,
        FontRow,
        MapRow,
        FieldStyleRow,
        FieldStyleButton,
        FieldType,

        ProspectDefaultFieldRow,
        ProspectMetaFieldRow,
        ProjectDefaultFieldRow,
        ProjectMetaFieldRow,
        OrderDefaultFieldRow,
        ProductDefaultFieldRow,
        CategoryRow,
        QuestionnaireRow,
        DateRow,

        AddFontModal,
        AddPageModal,
        OrderTableModal,

        VuePDF,
        Autocomplete,
    },

    data() {
        return {
            selectedFields: [],

            /**
             *
             */
            newField: null,

            /**
             * PDF task loader
             */
            pdf: null,

            // Replace by the real
            // list of document fields
            fakeOrderField: fakeOrderField,
            shownFields: [],

            dateFormats: ["DD/MM/YYYY", "DD-MM-YYYY", "DD", "MM", "YYYY"],
            zooms: [0.25, 0.5, 0.75, 1, 1.25, 1.5, 1.75, 2],

            loadingFonts: [],
            openModal: false,
            tab: 0,
            documentTemplateTab: 3,

            fileKeyword: "",
            fontKeyword: "",
            projectFieldKeyword: "",
            prospectFieldKeyword: "",
            categoryKeyword: "",
            questionnaireKeyword: "",
        };
    },

    mounted() {
        if (!this.mobile) {
            if (this.document) {
                store.commit(CLOSE_SLIDE, "documents");
                this.loadFonts();
                this.goToNextPage();
            } else {
                // store.commit(OPEN_SLIDE, "documents");
            }

            document.addEventListener("keydown", this.keydown);
            document.addEventListener("keyup", this.updateFields);
        }
    },

    methods: {
        keydown(e) {
            if (e.key == "ArrowUp") {
            } else if (e.key == "ArrowDown") {
            } else if (e.key == "ArrowLeft") {
            } else if (e.key == "ArrowRight") {
            }
        },

        /**
         * Add field to the selected fields
         * @param {*} field
         */
        setSelectedField(e, field) {
            this.selectedFields = [
                ...(e.ctrlKey ? this.selectedFields : []),
                field,
            ];
            store.commit(SET_DOCUMENT_FIELD, field);
        },

        /**
         * Check if field is selected
         * @param {*} field
         */
        isFieldSelected(field) {
            return this.selectedFields.find((f) => f.id == field.id);
        },

        /**
         *
         * @param {*} field
         */
        dragNewField(field) {
            this.newField = field;
        },

        /**
         * Drop field in pdf area
         */
        dropNewField(x, y) {
            if (!this.newField) {
                return;
            }

            // Recalculate field position
            // relative to the pdf area
            const pdfRect = this.$refs.pdf.getBoundingClientRect();
            const left = x - pdfRect.x;
            const top = y - pdfRect.y;

            // Do not add field outside of the area
            if (
                left >= 0 &&
                top >= 0 &&
                left <= pdfRect.width &&
                top <= pdfRect.height
            ) {
                this.addField(
                    this.newField,
                    left / this.documentZoom,
                    top / this.documentZoom
                );
            }

            this.newField = null;
        },

        /**
         *
         */
        placeNewField(e) {
            this.newField = {
                type: "html",
                content: "",
                style: {
                    left: e.clientX + "px",
                    top: e.clientY + "px",
                },
            };

            store.commit(SET_DOCUMENT_FIELD_EDIT, this.newField);
        },

        /**
         *
         */
        addPlacedNewField(field) {
            // Recalculate field position
            // relative to the pdf area
            const pdfRect = this.$refs.pdf.getBoundingClientRect();
            const left = parseInt(field.style.left) - pdfRect.x;
            const top = parseInt(field.style.top) - pdfRect.y;

            this.addField(
                field,
                left / this.documentZoom,
                top / this.documentZoom
            );

            this.newField = null;
        },

        /**
         *
         * Add field in pdf area
         * @param {*} field
         * @param {*} x x position
         * @param {*} y y position
         */
        async addField(field, x, y) {
            const f = {
                ...field,
                // Random ID
                id: parseInt(Math.random() * 100000),
                // Current page
                page_id: this.documentPage.id,
                // Position
                style: {
                    ...field.style,
                    ...(x ? { left: x + "px" } : {}),
                    ...(y ? { top: y + "px" } : {}),
                },
            };

            // Append field
            this.shownFields = [...this.shownFields, f];
            try {
                const addedField = await store.dispatch(ADD_DOCUMENT_FIELD, f);
                // Mark this field as selected field
                this.selectedFields = [addedField];
            } finally {
                this.shownFields = this.pageFields;
            }
        },

        /**
         * Get selected fields style
         *
         * @param {*} property css name
         */
        getSelectedFieldsStyle(property) {
            const style = cssProperties[property];
            if (!style) {
                return;
            }

            let value = null;

            // Check if all fields
            // have the same css value
            for (let i in this.selectedFields) {
                const field = this.selectedFields[i];
                const fieldStyle = field.style[style.name];

                if (!value) {
                    value =
                        fieldStyle === undefined
                            ? // default value
                              style.default
                            : style.get
                            ? style.get(fieldStyle)
                            : fieldStyle;
                } else if (value != fieldStyle) {
                    return undefined;
                }
            }

            return value;
        },

        /**
         * Set selected fields style
         *
         * @param {*} property css name
         * @param {*} value
         */
        setSelectedFieldsStyle(property, value) {
            const style = cssProperties[property];
            if (!style) {
                return;
            }

            this.selectedFields.forEach((field) => {
                field.style = {
                    ...field.style,
                    [style.name]: style.set
                        ? style.set(value, field.style[style.name])
                        : value,
                };
            });
        },

        /**
         *
         */
        updateFields() {
            this.selectedFields.forEach((field) => {
                store.dispatch(UPDATE_DOCUMENT_FIELD, {
                    id: field.id,
                    content: field.content,
                    style: field.style,
                });
            });
        },

        /**
         * Upload files
         * @param {*} event
         */
        uploadDroppedFiles(event) {
            const files = event.dataTransfer.files;

            for (var i = 0; i < files.length; i++) {
                this.uploadFile(files[i]);
            }
        },

        /**
         * Upload files
         * @param {*} event
         */
        uploadSelectedFiles(event) {
            const files = event.target.files;

            for (var i = 0; i < files.length; i++) {
                this.uploadFile(files[i]);
            }
        },

        /**
         * Upload file
         * @param {*} event
         */
        uploadFile(file) {
            // PDF file
            // append to the template PDFs
            if (file.type.match("pdf")) {
                this.documentTemplateTab = 0;
                this.uploadPdf(file);
                // Font file
                // Use as template font
            } else if (file.name.match("ttf")) {
                this.documentTemplateTab = 1;
                this.uploadFont(file);
            }
        },

        /**
         * Upload fields
         * @param {*} event
         */
        uploadDroppedFields(event) {
            const files = event.dataTransfer.files;

            for (var i = 0; i < files.length; i++) {
                const file = files[i];

                // Image files
                // Add as image field in pdf
                if (file.type.match("image")) {
                    const pdfRect = this.$refs.pdf.getBoundingClientRect();
                    const left = event.clientX - pdfRect.x;
                    const top = event.clientY - pdfRect.y;

                    this.uploadDroppedImage(file, left, top);
                    // PDF file
                    // append to the template PDFs
                }
            }

            const text = event.dataTransfer.getData("Text");
            if (text) {
                const pdfRect = this.$refs.pdf.getBoundingClientRect();
                const left = event.clientX - pdfRect.x;
                const top = event.clientY - pdfRect.y;

                this.dropText(text, left, top);
            }
        },

        /**
         * Upload fields
         * @param {*} event
         */
        uploadDroppedFields(event) {
            const files = event.dataTransfer.files;

            for (var i = 0; i < files.length; i++) {
                const file = files[i];

                // Image files
                // Add as image field in pdf
                if (file.type.match("image")) {
                    const pdfRect = this.$refs.pdf.getBoundingClientRect();
                    const left = event.clientX - pdfRect.x;
                    const top = event.clientY - pdfRect.y;

                    this.uploadDroppedImage(file, left, top);
                    // PDF file
                    // append to the template PDFs
                }
            }

            const text = event.dataTransfer.getData("Text");
            if (text) {
                const pdfRect = this.$refs.pdf.getBoundingClientRect();
                const left = event.clientX - pdfRect.x;
                const top = event.clientY - pdfRect.y;

                this.dropText(text, left, top);
            }
        },

        /**
         * Resize an image
         * @param {*} image
         * @param {*} maxSize
         */
        resizeImage(image, maxSize) {
            const size = Math.min(maxSize, Math.max(image.width, image.height));
            const aspect = image.width / image.height;
            const canvas = document.createElement("canvas");
            const context = canvas.getContext("2d");

            // Fit image in maxSize x maxSize frame
            if (aspect > 1) {
                canvas.width = size;
                canvas.height = size / aspect;
            } else {
                canvas.width = size * aspect;
                canvas.height = size;
            }

            context.drawImage(image, 0, 0, canvas.width, canvas.height);
            return canvas.toDataURL();
        },

        /**
         * Upload dropped image
         * @param {*} event
         */
        dropText(text, x, y) {
            this.addField(
                { type: "html", content: text, style: { width: "200px" } },
                x,
                y
            );
        },

        /**
         * Upload dropped image
         * @param {*} event
         */
        uploadDroppedImage(image, x, y) {
            // Read image
            const fileReader = new FileReader();
            fileReader.onload = () => {
                // Create image
                const image = new Image();
                image.onload = () => {
                    // Resize image
                    const type = "image";
                    const content = this.resizeImage(image, 300);

                    // Add as image field
                    this.addField({ type, content }, x, y);
                };
                image.src = fileReader.result;
            };
            fileReader.readAsDataURL(image);
        },

        /**
         * Upload PDF
         * @param {*} event
         */
        uploadPdf(pdf) {
            var formData = new FormData();
            formData.append("file", pdf);
            // dispatch document pdf
            store.dispatch(ADD_DOCUMENT_FILE, formData);
        },

        /**
         * Upload font
         * @param {*} event
         */
        async uploadFont(font) {
            var formData = new FormData();
            formData.append("file", font);

            try {
                // dispatch document font
                const f = await store.dispatch(ADD_DOCUMENT_FONT, formData);
                this.loadFont(f);
            } finally {
            }
        },

        /**
         * Load document font
         * @param {*} font
         */
        loadFont(font) {
            const name = font.name;
            const fontFace = new FontFace(name, "url(" + font.url + ")", {
                style: font.style,
                weight: font.weight,
            });
            document.fonts.add(fontFace);
            fontFace.load().then((ft) => {
                // fontFamilies.push(name);
            });
            this.loadingFonts = [...this.loadingFonts, font];
        },

        /**
         * Load document fonts
         * @param {*} font
         */
        loadFonts() {
            this.documentFonts
                .filter(
                    (f) => !this.loadingFonts.find((font) => font.id == f.id)
                )
                .forEach((font) => this.loadFont(font));
        },

        // Pages

        /**
         * Next document page
         */
        nextPage() {
            const index = this.documentPages.findIndex(
                (p) => p.id == (this.documentPage ? this.documentPage.id : 0)
            );

            if (this.documentPages.length > index + 1) {
                return this.documentPages[index + 1];
            }

            return null;
        },

        /**
         * Prev document page
         */
        prevPage() {
            const index = this.documentPages.indexOf(this.documentPage);

            if (index > 0) {
                return this.documentPages[index - 1];
            }

            return null;
        },

        /**
         *
         */
        goToNextPage() {
            store.commit(SHOW_DOCUMENT_PAGE, this.nextPage());
        },

        /**
         *
         */
        goToPrevPage() {
            store.commit(SHOW_DOCUMENT_PAGE, this.prevPage());
        },

        /**
         *
         */
        zoomIn() {
            if (this.documentZoom < 5) {
                store.commit(UPDATE_DOCUMENT_ZOOM, this.documentZoom + 0.125);
            }
        },

        /**
         *
         */
        zoomOut() {
            if (this.documentZoom > 0.5) {
                store.commit(UPDATE_DOCUMENT_ZOOM, this.documentZoom - 0.125);
            }
        },

        /**
         *
         */
        setZoom(zoom) {
            store.commit(UPDATE_DOCUMENT_ZOOM, zoom);
        },

        /**
         *
         */
        zoomInit() {
            store.commit(UPDATE_DOCUMENT_ZOOM, 1);
        },

        /**
         *
         */
        async pageMoved(evt) {
            const page = this.documentPages[evt.newDraggableIndex];
            page.page = evt.newDraggableIndex + 1;
            try {
                await store.dispatch(UPDATE_DOCUMENT_PAGE, page);
                store.dispatch(FETCH_DOCUMENT_PAGES);
            } finally {
            }
        },

        /**
         *
         */
        addPage() {
            store.commit(OPEN_MODAL, "document-page-add");
        },

        /**
         *
         */
        addA4PortraitPage() {
            store.commit(SET_DOCUMENT_NEW_PAGE, {
                width: "209.90277777778mm",
                height: "297.03888888889mm",
            });
            this.addPage();
        },

        /**
         *
         */
        addA4LandscapePortraitPage() {
            store.commit(SET_DOCUMENT_NEW_PAGE, {
                width: "297.03888888889mm",
                height: "209.90277777778mm",
            });
            this.addPage();
        },

        /**
         *
         */
        addFromFilePage(file) {
            store.commit(SET_DOCUMENT_NEW_PAGE, {
                file_id: file.id,
            });
            this.addPage();
        },

        /**
         *
         */
        addFont() {
            store.commit(OPEN_MODAL, "document-font-add");
        },

        /**
         *
         */
        createProspectField() {
            store.commit(OPEN_MODAL, "field-add");
        },

        /**
         *
         */
        createCategory() {
            store.commit(OPEN_MODAL, "category-add");
        },

        /**
         *
         */
        createQuestionnaire() {
            store.commit(OPEN_MODAL, "questionnaire-add");
        },

        editOrderTable() {
            store.commit(OPEN_MODAL, "document-order-table");
        },

        fileThumbnail(file) {
            return (
                API_URL +
                "/project/" +
                this.project.slug +
                "/document/" +
                file.document_id +
                "/file/" +
                file.id +
                "/page/1/thumbnail"
            );
        },
    },

    computed: {
        // All CSS properties
        // so we can use them
        // as input v-model
        ...Object.fromEntries(
            Object.keys(cssProperties).map((key) => [
                key,
                {
                    // Get selected field CSS value
                    get() {
                        return this.getSelectedFieldsStyle(key);
                    },
                    // Set selected field CSS value
                    set(value) {
                        this.setSelectedFieldsStyle(key, value);
                    },
                },
            ])
        ),

        scale() {
            return 1.5 * this.documentZoom;
        },

        /**
         *
         */
        documentFieldContent: {
            // Get selected page
            get() {
                if (this.selectedFields.length != 1) {
                    return "";
                }

                return this.selectedFields[0].content;
            },
            // Set selected field page
            set(value) {
                if (this.selectedFields.length != 1) {
                    return;
                }

                this.selectedFields[0].content = value;
            },
        },

        /**
         *
         */
        zoom: {
            // Get selected page
            get() {
                return this.documentZoom;
            },
            // Set selected field page
            set(value) {
                this.setZoom(value);
            },
        },

        /**
         *
         */
        fieldType: {
            // Get selected page
            get() {
                let value = null;

                // Check if all fields
                // have the same css value
                for (let i in this.selectedFields) {
                    const field = this.selectedFields[i];

                    if (!value) {
                        value = field.type;
                    } else if (value != field.type) {
                        return undefined;
                    }
                }

                return value;
            },
            // Set selected field page
            set(value) {
                this.selectedFields.forEach((field) => {
                    field.type = value;
                });
            },
        },

        /**
         * Fields page
         */
        fieldsPage: {
            // Get selected page
            get() {
                let value;

                for (let i in this.selectedFields) {
                    const field = this.selectedFields[i];
                    if (value === undefined) {
                        value = field.page_id;
                    }

                    if (value != field.page_id) {
                        return -1;
                    }
                }

                return !value ? 0 : value;
            },
            // Set selected field page
            set(value) {
                if (!value) {
                    value = null;
                }

                this.selectedFields.forEach((field) => {
                    field.page_id = value;
                    store.dispatch(UPDATE_DOCUMENT_FIELD, {
                        id: field.id,
                        page_id: value,
                    });
                });
            },
        },

        /**
         * Current page fields
         */
        pageFields() {
            if (!this.documentPage) {
                return [];
            }

            return this.documentFields.filter(
                (field) =>
                    !field.page_id || field.page_id == this.documentPage.id
            );
        },

        /**
         * Current page pdf
         */
        pagePDFFile() {
            if (!this.documentPage) {
                return null;
            }

            if (!this.documentPage.file_id) {
                return null;
            }

            return this.documentFiles.find(
                (df) => df.id == this.documentPage.file_id
            );
        },

        /**
         * Current page pdf
         */
        pagePDFPage() {
            return this.documentPage.file_page;
        },

        /**
         *
         */
        documentDefaultProspectFields() {
            const keyword = removeStringAccent(this.prospectFieldKeyword);

            return this.fields.filter(
                (f) =>
                    !f.meta &&
                    f.for == "prospect" &&
                    removeStringAccent(f.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        documentMetaProspectFields() {
            const keyword = removeStringAccent(this.prospectFieldKeyword);

            return this.fields.filter(
                (f) =>
                    f.meta &&
                    f.for == "prospect" &&
                    removeStringAccent(f.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        documentDefaultProjectFields() {
            const keyword = removeStringAccent(this.projectFieldKeyword);

            return this.fields.filter(
                (f) =>
                    !f.meta &&
                    f.for == "project" &&
                    removeStringAccent(f.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        documentMetaProjectFields() {
            const keyword = removeStringAccent(this.projectFieldKeyword);

            return this.fields.filter(
                (f) =>
                    f.meta &&
                    f.for == "project" &&
                    removeStringAccent(f.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        documentDefaultOrderFields() {
            return [
                {
                    name: "Nom",
                    slug: "name",
                },
                {
                    name: "Description",
                    slug: "description",
                },
                {
                    name: "Devise",
                    slug: "currency",
                },
                {
                    name: "Prix Total HT",
                    slug: "total_excluding_tax",
                },
                {
                    name: "Prix Total TTC",
                    slug: "total_including_tax",
                },
                {
                    name: "Taxe Total",
                    slug: "tax_value",
                },
            ];
        },

        /**
         *
         */
        documentDefaultProductFields() {
            let fields = [
                {
                    name: "Nom",
                    slug: "name",
                },
                {
                    name: "Description",
                    slug: "description",
                },
                {
                    name: "Devise",
                    slug: "currency",
                },
                {
                    name: "Prix",
                    slug: "price",
                },
                {
                    name: "Taxe %",
                    slug: "tax_percent",
                },
                {
                    name: "Prix (valeur)",
                    slug: "price_value",
                },
                {
                    name: "Prix sans taxe",
                    slug: "price_excluding_tax",
                },
                {
                    name: "Prix avec taxe",
                    slug: "price_including_tax",
                },
                {
                    name: "Taxe (valeur)",
                    slug: "tax_value",
                },

                {
                    name: "Devis > Quantité",
                    slug: "quantity",
                    pivot: true,
                },
                {
                    name: "Devis > Devise",
                    slug: "currency",
                    pivot: true,
                },
                {
                    name: "Devis > Prix",
                    slug: "price",
                    pivot: true,
                },
                {
                    name: "Devis > Taxe %",
                    slug: "tax_percent",
                    pivot: true,
                },
                {
                    name: "Devis > Prix (valeur)",
                    slug: "price_value",
                    pivot: true,
                },
                {
                    name: "Devis > Prix sans taxe",
                    slug: "price_excluding_tax",
                    pivot: true,
                },
                {
                    name: "Devis > Prix avec taxe",
                    slug: "price_including_tax",
                    pivot: true,
                },
                {
                    name: "Devis > Taxe (valeur)",
                    slug: "tax_value",
                    pivot: true,
                },
            ];

            fields = [
                ...fields,
                ...this.orderProductFields.map((f) => ({
                    slug: f.slug,
                    name: f.name,
                    pivot: true,
                    meta: true,
                })),
            ];

            return fields;
        },

        /**
         *
         */
        fonts() {
            const fonts = this.documentFonts.map((f) => f.name);
            return fonts.filter((f, i) => fonts.indexOf(f) == i);
        },

        // All CSS properties
        // so we can use them
        // as input v-model
        ...Object.fromEntries(
            ["zoom", "type"].map((key) => [
                "map" + key,
                {
                    // Get selected field CSS value
                    get() {
                        let value = null;

                        // Check if all fields
                        // have the same css value
                        for (let i in this.selectedFields) {
                            const field = this.selectedFields[i];
                            const v = JSON.parse(field.content)[key];

                            if (!value) {
                                value = v;
                            } else if (value != v) {
                                return undefined;
                            }
                        }

                        return value;
                    },
                    // Set selected field CSS value
                    set(value) {
                        this.selectedFields.forEach((field) => {
                            const content = JSON.parse(field.content);
                            content[key] = value;
                            field.content = JSON.stringify(content);
                        });
                    },
                },
            ])
        ),

        // Field type
        ...Object.fromEntries(
            ["qrcode", "text", "html"].map((key) => [
                "fieldType" + key,
                {
                    // Get field type value
                    get() {
                        let value = null;

                        // Check if all fields
                        // have the same css value
                        for (let i in this.selectedFields) {
                            const field = this.selectedFields[i];

                            if (!value) {
                                value = field.type;
                            } else if (value != field.type) {
                                return false;
                            }
                        }

                        return value == key;
                    },
                    // Set field type value
                    set(value) {
                        const v = value ? key : "text";

                        this.selectedFields.forEach((field) => {
                            field.type = v;
                        });
                    },
                },
            ])
        ),

        // Border style
        ...Object.fromEntries(
            ["solid", "dotted", "dashed"].map((key) => [
                "borderStyle" + key,
                {
                    // Get field type value
                    get() {
                        return (
                            this.getSelectedFieldsStyle("borderStyle") == key
                        );
                    },
                    // Set field type value
                    set(value) {
                        this.setSelectedFieldsStyle(
                            "borderStyle",
                            value ? key : "none"
                        );
                    },
                },
            ])
        ),

        /**
         *
         */
        selectedFieldsHasBorder() {
            return (
                this.borderWidth ||
                this.borderLeftWidth ||
                this.borderTopWidth ||
                this.borderRightWidth ||
                this.borderBottomWidth
            );
        },

        /**
         *
         */
        filteredFiles() {
            const keyword = removeStringAccent(this.fileKeyword);

            return this.documentFiles.filter(
                (file) => removeStringAccent(file.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredFonts() {
            const keyword = removeStringAccent(this.fontKeyword);

            return this.documentFonts.filter(
                (font) => removeStringAccent(font.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredCategories() {
            const keyword = removeStringAccent(this.categoryKeyword);

            return this.categories.filter(
                (category) =>
                    removeStringAccent(category.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredQuestionnaires() {
            const keyword = removeStringAccent(this.questionnaireKeyword);

            return this.questionnaires.filter(
                (questionnaire) =>
                    removeStringAccent(questionnaire.name).indexOf(keyword) >= 0
            );
        },

        orderProductFields() {
            return this.fields.filter((f) => f.for == "order-product");
        },

        renderers() {
            return [
                OrderTableRenderer(fakeOrder, {
                    orderProduct: this.orderProductFields,
                }),
                DateRenderer(new Date()),
                FieldNameRenderer(),
                MapRenderer(37.7749, -122.4194),
                PageNumberRenderer(),
                ProjectFieldRenderer(),
                ProspectCategoryRenderer(),
                OrderFieldRenderer(
                    fakeOrder,
                    this.documentDefaultProductFields
                ),
                ProspectFieldRenderer(),
                InvoiceFieldRenderer(),
                ProspectResponseRenderer(),
                TextFieldRenderer(),
                UserFieldRenderer(),

                ProspectCategoryLabelRenderer(),
                ProspectResponseOptionRenderer(),
                ImageRenderer(),
                QrcodeRenderer(),
            ];
        },

        /**
         *
         */
        thumbnail() {
            return this.project && this.project.thumbnail
                ? this.project.thumbnail
                : window.location.origin + "/images/logo.png";
        },

        mobile() {
            return deviceType() != "desktop";
        },

        // Store
        ...mapGetters([
            "project",
            "fields",
            "categories",
            "questionnaires",
            "document",
            "documentFields",
            "documentField",
            "documentFiles",
            "documentFonts",
            "documentPages",
            "documentPage",
            "documentZoom",
        ]),
    },

    watch: {
        /**
         * Files
         */
        document() {
            store.commit(CLOSE_SLIDE, "documents");
        },

        /**
         * Files
         */
        documentFiles() {
            if (!this.document) {
                return;
            }

            store.dispatch(FETCH_DOCUMENT_PAGES);
        },

        /**
         * Pages
         */
        documentPages(newValue) {
            if (newValue.length > 0 && !this.documentPage) {
                this.goToNextPage();
            }
        },

        /**
         * Current page fields
         */
        pageFields(newValue) {
            this.shownFields = newValue;
            if (newValue.length > 0 && !this.documentField) {
                this.selectedFields = [newValue[0]];
                store.commit(SET_DOCUMENT_FIELD, newValue[0]);
            }
        },

        /**
         * Current page fields
         */
        async pagePDFFile(newValue) {
            if (!newValue) {
                this.pdf = null;
                return;
            }

            const { pdf } = usePDF(newValue.url + "v=" + newValue.updated_at);
            this.pdf = pdf;
        },

        document(newValue) {
            if (newValue) {
                this.loadFonts();
            }
        },
    },
};
</script>
