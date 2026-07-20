<template>
    <header id="hc-main-layout-header" :class="showMenus ? 'show-menus' : ''">
        <header-menu
            v-if="!project || !project.thumbnail"
            id="hc-main-layout-header-toggle-menus"
            tag="a"
            @click.prevent.stop="toggleMenu"
            icon="fa fa-list"
            color="#7939b8"
            style="width: 40px !important"
        />
        <icon
            v-else
            id="hc-main-layout-header-toggle-menus"
            tag="a"
            style="border-radius: 50%; width: 30px; height: 30px; margin: 5px"
            @click.prevent.stop="toggleMenu"
        >
            <img :src="project.thumbnail" :alt="project.name"
        /></icon>
        <nav id="hc-main-layout-header-menus" @click.prevent="toggleMenu">
            <header-menu
                tag="a"
                id="hc-main-layout-header-close"
                icon="fa fa-close"
                :label="$t('header.close')"
                color="#7939b8"
                @click.prevent.stop="toggleMenu"
            />
            <header-menu
                icon="fa fa-home"
                :label="$t('header.home')"
                color="#7939b8"
                :to="{ name: 'dashboard' }"
            />
            <template v-if="project">
                <header-menu
                    tag="a"
                    icon="fa fa-caret-left"
                    color="#888888"
                    @click.prevent.stop="$router.back()"
                />
                <!-- Prospect -->

                <header-menu
                    :label="$t('header.prospects')"
                    color="#12a0f3"
                    :to="{
                        name: 'prospect',
                        params: {
                            project: project.slug,
                        },
                        query: defaultMenuParams,
                    }"
                    v-tuto="{
                        key: 'project.header.menu.prospects',
                        name: $t('tutorial.project_header_menu_prospects.name'),
                        body:
                            '<img style=&quot;width: 100%;margin-bottom: 10px;border-radius: 5px;&quot; src=&quot;/images/tutorial/header.prospects.gif&quot; /><br>' +
                            $t('tutorial.project_header_menu_prospects.body.0'),
                    }"
                    @click="initProspectsParam"
                >
                    <img
                        v-if="projectUserSettingsMenuIconUrl('prospects')"
                        :src="projectUserSettingsMenuIconUrl('prospects')"
                    />
                    <svg
                        v-else
                        version="1.1"
                        xmlns="http://www.w3.org/2000/svg"
                        xmlns:xlink="http://www.w3.org/1999/xlink"
                        x="0px"
                        y="0px"
                        viewBox="0 0 500 500"
                        enable-background="new 0 0 500 500"
                        xml:space="preserve"
                    >
                        <g>
                            <circle fill="#1A73E8" cx="250" cy="250" r="250" />
                            <path
                                id="body_shadow"
                                fill="#185ABC"
                                d="M332.4,375H170.5c-19.6,0-36.9-11.8-36.9-31.2V358c0,19.4,17.3,34.1,36.9,34.1h161.9 c19.6,0,36.9-14.7,36.9-34.1v-14.2C369.3,363.2,352,375,332.4,375z"
                            />
                            <path
                                id="head_shadow"
                                fill="#185ABC"
                                d="M250,227.3c-29.3-0.1-52.4-20.4-54-48.3v11.4c0,28.9,24.2,54,54,54s54-25.1,54-54V179 C301.7,207.1,279.3,227.4,250,227.3z"
                            />
                            <g id="subject">
                                <path
                                    id="body"
                                    fill="#FFFFFF"
                                    d="M250,261.4c-57,0-116.5,27.8-116.5,65.3v17c0,19.4,15.9,34.1,35.5,34.1h164.7 c19.6,0,35.5-14.7,35.5-34.1v-17C369.3,289.1,307,261.4,250,261.4z"
                                />
                                <circle
                                    id="head"
                                    fill="#FFFFFF"
                                    cx="250"
                                    cy="176.1"
                                    r="54"
                                />
                            </g>
                        </g>
                    </svg>
                </header-menu>

                <!-- Event -->

                <header-menu
                    :label="$t('header.appointments')"
                    :to="{
                        name: 'event',
                        params: {
                            project: project.slug,
                        },
                        query: {
                            filters: JSON.stringify({
                                withUsers: projectUserSettingsAgendaUsers
                                    ? projectUserSettingsAgendaUsers
                                    : [this.$store.state.auth.user.id],
                            }),
                        },
                    }"
                    v-tuto="{
                        key: 'project.header.menu.events',
                        name: $t('tutorial.project_header_menu_events.name'),
                        body:
                            $t('tutorial.project_header_menu_events.body.0') +
                            '<img style=&quot;width: 100%;margin-top: 10px;border-radius: 5px;&quot; src=&quot;/images/tutorial/header.events.gif&quot; />',
                    }"
                >
                    <img
                        v-if="projectUserSettingsMenuIconUrl('events')"
                        :src="projectUserSettingsMenuIconUrl('events')"
                    />
                    <svg
                        v-else
                        version="1.1"
                        xmlns="http://www.w3.org/2000/svg"
                        xmlns:xlink="http://www.w3.org/1999/xlink"
                        x="0px"
                        y="0px"
                        viewBox="0 0 200 200"
                        enable-background="new 0 0 200 200"
                        xml:space="preserve"
                    >
                        <g>
                            <g transform="translate(3.75 3.75)">
                                <path
                                    fill="#FFFFFF"
                                    d="M148.882,43.618l-47.368-5.263l-57.895,5.263L38.355,96.25l5.263,52.632l52.632,6.579l52.632-6.579 l5.263-53.947L148.882,43.618z"
                                />
                                <path
                                    fill="#1A73E8"
                                    d="M65.211,125.276c-3.934-2.658-6.658-6.539-8.145-11.671l9.132-3.763c0.829,3.158,2.276,5.605,4.342,7.342 c2.053,1.737,4.553,2.592,7.474,2.592c2.987,0,5.553-0.908,7.697-2.724s3.224-4.132,3.224-6.934c0-2.868-1.132-5.211-3.395-7.026 s-5.105-2.724-8.5-2.724h-5.276v-9.039H76.5c2.921,0,5.382-0.789,7.382-2.368c2-1.579,3-3.737,3-6.487 c0-2.447-0.895-4.395-2.684-5.855s-4.053-2.197-6.803-2.197c-2.684,0-4.816,0.711-6.395,2.145s-2.724,3.197-3.447,5.276 l-9.039-3.763c1.197-3.395,3.395-6.395,6.618-8.987c3.224-2.592,7.342-3.895,12.342-3.895c3.697,0,7.026,0.711,9.974,2.145 c2.947,1.434,5.263,3.421,6.934,5.947c1.671,2.539,2.5,5.382,2.5,8.539c0,3.224-0.776,5.947-2.329,8.184 c-1.553,2.237-3.461,3.947-5.724,5.145v0.539c2.987,1.25,5.421,3.158,7.342,5.724c1.908,2.566,2.868,5.632,2.868,9.211 s-0.908,6.776-2.724,9.579c-1.816,2.803-4.329,5.013-7.513,6.618c-3.197,1.605-6.789,2.421-10.776,2.421 C73.408,129.263,69.145,127.934,65.211,125.276z"
                                />
                                <path
                                    fill="#1A73E8"
                                    d="M121.25,79.961l-9.974,7.25l-5.013-7.605l17.987-12.974h6.895v61.197h-9.895L121.25,79.961z"
                                />
                                <path
                                    fill="#EA4335"
                                    d="M148.882,196.25l47.368-47.368l-23.684-10.526l-23.684,10.526l-10.526,23.684L148.882,196.25z"
                                />
                                <path
                                    fill="#34A853"
                                    d="M33.092,172.566l10.526,23.684h105.263v-47.368H43.618L33.092,172.566z"
                                />
                                <path
                                    fill="#4285F4"
                                    d="M12.039-3.75C3.316-3.75-3.75,3.316-3.75,12.039v136.842l23.684,10.526l23.684-10.526V43.618h105.263 l10.526-23.684L148.882-3.75H12.039z"
                                />
                                <path
                                    fill="#188038"
                                    d="M-3.75,148.882v31.579c0,8.724,7.066,15.789,15.789,15.789h31.579v-47.368H-3.75z"
                                />
                                <path
                                    fill="#FBBC04"
                                    d="M148.882,43.618v105.263h47.368V43.618l-23.684-10.526L148.882,43.618z"
                                />
                                <path
                                    fill="#1967D2"
                                    d="M196.25,43.618V12.039c0-8.724-7.066-15.789-15.789-15.789h-31.579v47.368H196.25z"
                                />
                            </g>
                        </g>
                    </svg>
                </header-menu>

                <!-- MAP -->

                <header-menu
                    v-if="can('all.map')"
                    :label="$t('header.map')"
                    v-tuto="{
                        key: 'project.header.menu.map',
                        name: $t('tutorial.project_header_menu_map.name'),
                        body:
                            $t('tutorial.project_header_menu_map.body.0') +
                            '<img style=&quot;width: 100%;margin-top: 10px;border-radius: 5px;&quot; src=&quot;/images/tutorial/header.map.gif&quot; />',
                    }"
                    :to="{
                        name: 'map',
                        params: {
                            project: project.slug,
                        },
                        query: {
                            count: 1000,
                        },
                    }"
                >
                    <img
                        v-if="projectUserSettingsMenuIconUrl('map')"
                        :src="projectUserSettingsMenuIconUrl('map')" />
                    <svg
                        v-else
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 92.3 132.3"
                    >
                        <path
                            fill="#1a73e8"
                            d="M60.2 2.2C55.8.8 51 0 46.1 0 32 0 19.3 6.4 10.8 16.5l21.8 18.3L60.2 2.2z"
                        />
                        <path
                            fill="#ea4335"
                            d="M10.8 16.5C4.1 24.5 0 34.9 0 46.1c0 8.7 1.7 15.7 4.6 22l28-33.3-21.8-18.3z"
                        />
                        <path
                            fill="#4285f4"
                            d="M46.2 28.5c9.8 0 17.7 7.9 17.7 17.7 0 4.3-1.6 8.3-4.2 11.4 0 0 13.9-16.6 27.5-32.7-5.6-10.8-15.3-19-27-22.7L32.6 34.8c3.3-3.8 8.1-6.3 13.6-6.3"
                        />
                        <path
                            fill="#fbbc04"
                            d="M46.2 63.8c-9.8 0-17.7-7.9-17.7-17.7 0-4.3 1.5-8.3 4.1-11.3l-28 33.3c4.8 10.6 12.8 19.2 21 29.9l34.1-40.5c-3.3 3.9-8.1 6.3-13.5 6.3"
                        />
                        <path
                            fill="#34a853"
                            d="M59.1 109.2c15.4-24.1 33.3-35 33.3-63 0-7.7-1.9-14.9-5.2-21.3L25.6 98c2.6 3.4 5.3 7.3 7.9 11.3 9.4 14.5 6.8 23.1 12.8 23.1s3.4-8.7 12.8-23.2"
                        /></svg
                ></header-menu>

                <!-- Mail -->

                <header-menu
                    :label="$t('header.mail')"
                    color="#12a0f3"
                    :to="{
                        name: 'prospect',
                        params: {
                            project: project.slug,
                        },
                        query: {
                            filters: JSON.stringify({
                                waitingUsers: [user.id],
                            }),
                        },
                    }"
                    @click.prevent="showWaitingUsers"
                >
                    <img
                        v-if="projectUserSettingsMenuIconUrl('mails')"
                        :src="projectUserSettingsMenuIconUrl('mails')"
                    />
                    <svg
                        v-else
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="52 42 88 66"
                    >
                        <path
                            fill="#4285f4"
                            d="M58 108h14V74L52 59v43c0 3.32 2.69 6 6 6"
                        />
                        <path
                            fill="#34a853"
                            d="M120 108h14c3.32 0 6-2.69 6-6V59l-20 15"
                        />
                        <path
                            fill="#fbbc04"
                            d="M120 48v26l20-15v-8c0-7.42-8.47-11.65-14.4-7.2"
                        />
                        <path
                            fill="#ea4335"
                            d="M72 74V48l24 18 24-18v26L96 92"
                        />
                        <path
                            fill="#c5221f"
                            d="M52 51v8l20 15V48l-5.6-4.2c-5.94-4.45-14.4-.22-14.4 7.2"
                        />
                    </svg>
                    <div
                        v-if="waitingUser"
                        v-text="waitingUser"
                        style="
                            display: inline-block;
                            position: absolute;
                            background-color: red;
                            color: white;
                            padding: 0 5px !important;
                            top: 4px;
                            right: -4px;
                            border-radius: 50%;
                            height: 20px;
                            line-height: 20px;
                            font-size: 11px;
                            font-weight: bold;
                        "
                    ></div>
                </header-menu>

                <!-- Order -->

                <header-menu
                    :label="$t('header.order')"
                    :to="{
                        name: 'order',
                        params: {
                            project: project.slug,
                        },
                    }"
                    v-tuto="{
                        key: 'project.header.menu.orders',
                        name: $t('tutorial.project_header_menu_orders.name'),
                        body: $t('tutorial.project_header_menu_orders.body.0'),
                    }"
                >
                    <img
                        v-if="projectUserSettingsMenuIconUrl('orders')"
                        :src="projectUserSettingsMenuIconUrl('orders')"
                    />
                    <svg
                        v-else
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 100 100"
                        style="enable-background: new 0 0 100 100"
                        xml:space="preserve"
                    >
                        <circle
                            style="fill: #547dbf"
                            cx="23.4"
                            cy="78"
                            r="11"
                        />
                        <circle
                            style="fill: #ea4435"
                            cx="70.1"
                            cy="78"
                            r="11"
                        />
                        <path
                            style="fill: #f9bc15"
                            d="M0,14.6l5.8,39C6.6,59,11.8,63,18.1,63h55.5c5.4,0,10.3-3.1,11.9-7.6l14.4-40.3c0.7-2.1-1-4.1-3.5-4.1H3.7 C1.5,11-0.3,12.7,0,14.6z"
                        />
                    </svg>
                </header-menu>

                <!-- Stat -->

                <header-menu
                    v-if="can('all.stat')"
                    :label="$t('header.stat')"
                    :to="{
                        name: 'stat',
                        params: {
                            project: project.slug,
                        },
                    }"
                    v-tuto="{
                        key: 'project.header.menu.stat',
                        name: $t('tutorial.project_header_menu_stat.name'),
                        body: $t('tutorial.project_header_menu_stat.body.0'),
                    }"
                >
                    <img
                        v-if="projectUserSettingsMenuIconUrl('stats')"
                        :src="projectUserSettingsMenuIconUrl('stats')"
                    />
                    <svg
                        v-else
                        version="1.1"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 80 80"
                        style="enable-background: new 0 0 80 80"
                        xml:space="preserve"
                    >
                        <g
                            id="g6628"
                            transform="matrix(3.5485559,0,0,3.5485559,-174.94921,-339.18037)"
                        >
                            <path
                                id="path3806"
                                style="fill: #f6ab1b"
                                d="M64.8,98.3v16.9c0,1.9,1.3,2.9,2.7,2.9c1.3,0,2.7-0.9,2.7-2.9V98.4c0-1.7-1.3-2.8-2.7-2.8 S64.8,96.8,64.8,98.3z"
                            />
                            <path
                                id="path3808"
                                style="fill: #e37525"
                                d="M57.8,106.9v8.3c0,1.9,1.3,2.9,2.7,2.9c1.3,0,2.7-0.9,2.7-2.9V107c0-1.7-1.3-2.8-2.7-2.8 S57.8,105.4,57.8,106.9L57.8,106.9z"
                            />
                            <path
                                id="path3810"
                                style="fill: #e37525"
                                d="M56.1,115.4c0,1.5-1.2,2.7-2.7,2.7c-1.5,0-2.7-1.2-2.7-2.7c0-1.5,1.2-2.7,2.7-2.7 C54.9,112.7,56.1,114,56.1,115.4"
                            />
                        </g>
                    </svg>
                </header-menu>

                <!-- Document -->

                <header-menu
                    v-if="can('all.project.document.view')"
                    :label="$t('header.documents')"
                    :to="{
                        name: 'document',
                        params: {
                            project: project.slug,
                        },
                    }"
                    v-tuto="{
                        key: 'project.header.menu.document',
                        name: $t('tutorial.project_header_menu_document.name'),
                        body:
                            '<img style=&quot;width: 100%;margin-bottom: 10px;border-radius: 5px;&quot; src=&quot;/images/tutorial/header.documents.gif&quot; /><br>' +
                            $t('tutorial.project_header_menu_document.body.0'),
                    }"
                >
                    <img
                        v-if="projectUserSettingsMenuIconUrl('documents')"
                        :src="projectUserSettingsMenuIconUrl('documents')"
                    />
                    <svg
                        v-else
                        version="1.1"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 256 256"
                        style="enable-background: new 0 0 256 256"
                        xml:space="preserve"
                    >
                        <path
                            fill="#FA0F00"
                            d="M45.257 0h165.485C235.886 0 256 20.114 256 45.257v165.486C256 235.886 235.886 256 210.742 256H45.257C20.114 256 0 235.886 0 210.743V45.257C0 20.114 20.114 0 45.257 0z"
                        />
                        <path
                            fill="#FFF"
                            d="M204.144 147.657c-11.887-12.343-44.344-7.314-52.115-6.4-11.428-10.972-19.199-24.229-21.942-28.8 4.114-12.343 6.856-24.686 7.314-37.942 0-11.429-4.571-23.771-17.372-23.771-4.571 0-8.686 2.742-10.972 6.399-5.485 9.601-3.2 28.801 5.486 48.458-5.028 14.171-9.6 27.885-22.4 52.114-13.257 5.484-41.143 18.285-43.429 32-.914 4.113.457 8.229 3.657 11.428 3.2 2.743 7.314 4.114 11.429 4.114 16.914 0 33.371-23.313 44.8-42.972 9.6-3.199 24.686-7.771 39.772-10.514 17.828 15.543 33.371 17.828 41.6 17.828 10.971 0 15.086-4.571 16.457-8.686 2.285-4.57.914-9.599-2.285-13.256zm-11.43 7.772c-.457 3.2-4.57 6.399-11.885 4.571-8.686-2.285-16.457-6.4-23.314-11.886 5.943-.915 19.199-2.286 28.8-.457 3.657.914 7.314 3.2 6.399 7.772zm-76.342-94.172c.914-1.371 2.286-2.285 3.657-2.285 4.114 0 5.028 5.028 5.028 9.143-.457 9.601-2.286 19.2-5.485 28.343-6.858-18.286-5.486-31.087-3.2-35.201zm-.914 88.686c3.657-7.314 8.686-20.115 10.514-25.601 4.114 6.856 10.971 15.086 14.629 18.743 0 .458-14.172 3.2-25.143 6.858zm-26.972 18.286C77.972 185.6 67 196.571 61.057 196.571c-.914 0-1.829-.457-2.743-.914-1.372-.915-1.829-2.286-1.372-4.114 1.372-6.4 13.258-15.086 31.544-23.314z"
                        />
                    </svg>
                </header-menu>

                <!-- Campaign -->

                <header-menu
                    v-if="can('all.project.campaign.view')"
                    :label="$t('header.campaigns')"
                    :to="{
                        name: 'campaign',
                        params: {
                            project: project.slug,
                        },
                    }"
                    v-tuto="{
                        key: 'project.header.menu.campaign',
                        name: $t('tutorial.project_header_menu_campaign.name'),
                        body:
                            $t('tutorial.project_header_menu_campaign.body.0') +
                            '<br><img style=&quot;width: 100%;margin: 10px 0;border-radius: 5px;&quot; src=&quot;/images/tutorial/header.campaigns.gif&quot; /><br><ul><li>' +
                            $t('tutorial.project_header_menu_campaign.body.1') +
                            '</li><li>' +
                            $t('tutorial.project_header_menu_campaign.body.2') +
                            '</li><li>' +
                            $t('tutorial.project_header_menu_campaign.body.3') +
                            '</li><li>...</li></ul>',
                    }"
                >
                    <img
                        v-if="projectUserSettingsMenuIconUrl('campaigns')"
                        :src="projectUserSettingsMenuIconUrl('campaigns')"
                    />
                    <svg
                        v-else
                        version="1.1"
                        xmlns="http://www.w3.org/2000/svg"
                        xmlns:xlink="http://www.w3.org/1999/xlink"
                        x="0px"
                        y="0px"
                        viewBox="0 0 100 100"
                        style="enable-background: new 0 0 100 100"
                        xml:space="preserve"
                    >
                        <path
                            style="fill: #ea4435"
                            d="M17,68.9c-4.1,0-7.9-1.5-10.8-4L1.7,85.9c-0.5,2.3,1,4.5,3.2,5l6.9,1.5c2.3,0.5,4.5-1,5-3.2l4.3-20.2H17z"
                        />
                        <path
                            style="fill: #33a852"
                            d="M70.5,15c-0.1,0-0.1,0-0.2,0c-3.4,0.5-6.2,15.7-6.2,34.3c0,18.5,2.7,33.6,6.2,34.3c0.1,0,0.2,0,0.2,0 c3.5,0,6.4-15.4,6.4-34.3S74.1,15,70.5,15z"
                        />
                        <circle
                            style="fill: #f9bc15"
                            cx="67.3"
                            cy="48.9"
                            r="11.8"
                        />
                        <path
                            style="fill: #547dbf"
                            d="M64.2,49.4c0-18.6,2.8-33.8,6.2-34.3c-0.1,0-0.2,0-0.3,0c-0.7,0-1.4,0.2-2.1,0.6L47.4,27.5 c-3.7,2.1-7.8,3.2-12.1,3.2h-6.3h0H17c-9.2,0-16.6,7.4-16.6,16.6v5c0,9.2,7.4,16.6,16.6,16.6h11.9h0h8c4.2,0,8.4,1.1,12.1,3.2 l19,10.9c0.8,0.4,1.6,0.6,2.3,0.6C66.9,83,64.2,67.9,64.2,49.4z"
                        />
                        <path
                            style="fill: #f9bc15"
                            d="M80.9,71l10.2,5.9c2-2.8,3.8-5.9,5.1-9.1l-10.3-5.9C84.7,65.1,83,68.2,80.9,71z"
                        />
                        <path
                            style="fill: #f9bc15"
                            d="M85.9,35.9L96.2,30c-1.4-3.2-3.1-6.3-5.1-9.1l-10.2,5.9C83,29.5,84.7,32.6,85.9,35.9z"
                        />
                        <path
                            style="fill: #f9bc15"
                            d="M99.7,43.6H87.9c0.2,1.7,0.4,3.5,0.4,5.2c0,1.8-0.2,3.5-0.4,5.2h11.8c0.2-1.7,0.3-3.5,0.3-5.2 S99.9,45.4,99.7,43.6z"
                        />
                    </svg>
                </header-menu>
            </template>
        </nav>
        <div style="flex: 1; height: 100%; z-index: 5">
            <global-search
                v-tuto="
                    project
                        ? {
                              key: 'project.header.search',
                              name: $t('tutorial.project_header_search.name'),
                              body:
                                  '<b>CTRL + F</b><br>' +
                                  $t('tutorial.project_header_search.body.0') +
                                  '<br>' +
                                  $t('tutorial.project_header_search.body.1'),
                          }
                        : null
                "
            />
        </div>
        <header-menu
            tag="a"
            icon="fa fa-cog"
            @click.prevent="setting"
            style="width: 40px !important; height: 40px !important"
        />
        <!--header-menu
            tag="a"
            icon="fa fa-power-off"
            @click.prevent="logout"
            style="width: 40px !important"
        /-->
    </header>
</template>

<style>
#hc-main-layout-header {
    display: flex;
    flex-direction: row;
    height: 46px;
    width: 100%;
    background-color: white;
    padding: 3px;
    align-items: center;
}

#hc-main-layout-header-menus {
    display: flex;
    flex-direction: row;
    height: 100%;
    gap: 2px;
    align-items: center;
}

#hc-main-layout-header-toggle-menus {
    display: none;
}

#hc-main-layout-header-close {
    display: none;
}

@media (max-width: 767px) {
    /*
    #hc-main-layout-header-menus {
        position: fixed;
        left: 0;
        top: 0;
        height: 100%;
        width: 240px;
        background-color: white;
        z-index: 1000;
        flex-direction: column;
        transform: translateX(-100%);
        transition: all 100ms ease-out;
        padding: 10px;
    }

    .show-menus #hc-main-layout-header-menus {
        transform: translateX(0);
        box-shadow: 0 0 50px #0003;
    }

    #hc-main-layout-header-close {
        display: flex;
    }

    #hc-main-layout-header-toggle-menus {
        display: flex;
    }

    .hc-header-menu {
        width: 100%;
        display: flex;
    }

    .hc-header-menu > i,
    .hc-header-menu > svg,
    .hc-header-menu > img {
        width: 36px;
        height: 36px;
        padding: 7px;
        text-align: center;
        line-height: 22px;
    }

    .hc-header-menu > span {
        opacity: 1;
        visibility: visible;
        position: relative;
        flex: 1;
        padding-left: 5px;
        background: none;
        color: black;
        top: unset;
        left: 0;
        transform: translate(0);
        font-size: 12px;
    }

    .hc-header-menu:hover > span {
        transform: translateX(0);
    }*/
}
</style>

<script>
import { mapGetters, mapActions } from "vuex";
import store from "@/store";

import HeaderMenu from "./Menu.vue";
import GlobalSearch from "./Search.vue";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";
import {
    INIT_PROSPECT_PARAMS,
    SET_PROSPECT_PARAMS,
    FETCH_PROSPECTS,
} from "@/actions/project/prospect";

export default {
    components: { HeaderMenu, GlobalSearch },

    data() {
        return {
            showMenus: false,
            waitingUser: 0,
        };
    },

    mounted() {
        this.fetchWaitingUser();
    },

    methods: {
        ...mapActions({
            signOut: "auth/logout",
        }),
        addUser() {
            store.commit(OPEN_SLIDE, "manage-users");
        },
        async logout() {
            await this.signOut();
        },
        setting() {
            store.commit(OPEN_SLIDE, "settings");
        },
        toggleMenu() {
            this.showMenus = !this.showMenus;
        },
        showWaitingUsers() {
            let params = {
                waitingUsers: [this.user.id],
            };

            store.commit(INIT_PROSPECT_PARAMS);

            store.commit(SET_PROSPECT_PARAMS, params);
            store.dispatch(FETCH_PROSPECTS);
        },
        initProspectsParam() {
            if (this.$route.name == "prospect") {
                store.commit(INIT_PROSPECT_PARAMS);
                store.dispatch(FETCH_PROSPECTS);
            }
        },
        async fetchWaitingUser() {
            if (!this.project) {
                return;
            }

            try {
                const { data } = await axios.get(
                    `/api/project/${
                        this.project.slug
                    }/prospect/count?filters=${JSON.stringify({
                        waitingUsers: [this.user.id],
                    })}`
                );

                this.waitingUser = data;
            } finally {
                this.fetchingQrcode = false;
            }
        },
    },

    computed: {
        ...mapGetters("auth", ["user"]),
        ...mapGetters([
            "project",
            "projectUserSettingsProspectsDefaultMenu",
            "projectUserSettingsAgendaUsers",
            "projectUserSettingsMenuIconUrl",
            "menus",
            "can",
        ]),

        defaultMenuParams() {
            const query = { count: 50 };
            if (this.projectUserSettingsProspectsDefaultMenu) {
                return query;
            }

            const menu = this.menus.find(
                (m) => m.id == this.projectUserSettingsProspectsDefaultMenu
            );

            if (!menu) {
                return query;
            }

            return {
                ...query,
                filters: JSON.stringify(menu.filters),
            };
        },
    },
};
</script>
