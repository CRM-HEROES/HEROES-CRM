<template>
    <slide
        :name="name"
        @open="fetchInteractions(), fetchSelectedProspects()"
        @closed="resetCloudtalkSlideState"
        :title="
            $t('prospect.interaction.title', {
                prospect: interactionTitleProspect
                    ? interactionTitleProspect.last_name
                    : '',
            })
        "
        :url="
            interactionTitleProspect
                ? {
                      name: 'prospect.show',
                      params: {
                          project: project.slug,
                          prospect: interactionTitleProspect.id,
                      },
                  }
                : null
        "
        :left="true"
        :eager="true"
        icon="fa fa-phone"
        :style="{ width: slideWidth }"
    >
        <div class="hc-prospect-interaction-root">
            <div
                :class="[
                    'hc-prospect-interaction-cloudtalk-panel',
                    {
                        visible: cloudtalkPhoneDisplayed,
                        'with-context': cloudtalkCallContextVisible,
                    },
                ]"
            >
                <cloudtalk
                    id="cloudtalk-phone"
                    class="hc-prospect-interaction-cloudtalk-phone"
                    :number="interaction.number"
                    :calling="callingCloudtalk"
                    :loading="cloudtalkLoading"
                    @make-call="makeCloudtalkCall"
                    @call-activity="displayCloudtalkPhoneFromIframe"
                    @ringing-call="cloudtalkCallRinging"
                    @outgoing-call="cloudtalkCallOutgoing"
                    @call-ended="cloudtalkCallEnded"
                    @hangup-call="cloudtalkCallHangup"
                    @answered-call="cloudtalkCallAnswered"
                    @contact-info="cloudtalkCallContactInfo"
                />

                <aside
                    v-if="cloudtalkCallContextVisible"
                    class="hc-prospect-interaction-cloudtalk-context"
                >
                    <div
                        v-if="cloudtalkLookupLoading && !cloudtalkCallProspect"
                        class="hc-prospect-interaction-cloudtalk-context-loading"
                    >
                        <loading :loading="cloudtalkLookupLoading" />
                    </div>

                    <template v-else-if="cloudtalkCallProspect">
                        <div class="hc-prospect-interaction-cloudtalk-context-header">
                            <div>
                                <div
                                    class="hc-prospect-interaction-cloudtalk-context-title"
                                    v-text="cloudtalkProspectName"
                                ></div>
                                <div
                                    v-if="cloudtalkCallProspect.company_name"
                                    class="hc-prospect-interaction-cloudtalk-context-subtitle"
                                    v-text="cloudtalkCallProspect.company_name"
                                ></div>
                            </div>
                            <router-link
                                class="hc-prospect-interaction-cloudtalk-context-link"
                                :to="{
                                    name: 'prospect.show',
                                    params: {
                                        project: project.slug,
                                        prospect: cloudtalkCallProspect.id,
                                    },
                                }"
                            >
                                <icon class="fa fa-external-link" />
                            </router-link>
                        </div>

                        <div class="hc-prospect-interaction-cloudtalk-context-section">
                            <div
                                v-if="cloudtalkCallProspect.email"
                                class="hc-prospect-interaction-cloudtalk-context-line"
                            >
                                <icon class="fa fa-envelope" />
                                <span v-text="cloudtalkCallProspect.email"></span>
                            </div>
                            <div
                                v-if="cloudtalkCallProspect.phone_number"
                                class="hc-prospect-interaction-cloudtalk-context-line"
                            >
                                <icon class="fa fa-phone" />
                                <span
                                    v-text="cloudtalkCallProspect.phone_number"
                                ></span>
                            </div>
                            <div
                                v-if="cloudtalkCallProspect.mobile_phone_number"
                                class="hc-prospect-interaction-cloudtalk-context-line"
                            >
                                <icon class="fa fa-mobile" />
                                <span
                                    v-text="
                                        cloudtalkCallProspect.mobile_phone_number
                                    "
                                ></span>
                            </div>
                        </div>

                        <div class="hc-prospect-interaction-cloudtalk-context-section">
                            <div class="hc-prospect-interaction-cloudtalk-context-heading">
                                information
                            </div>
                            <div
                                v-if="cloudtalkLookupThreads.length == 0"
                                class="hc-prospect-interaction-cloudtalk-context-empty"
                            >
                                Aucun information lie a votre utilisateur.
                            </div>
                            <div
                                v-for="thread in cloudtalkLookupThreads"
                                :key="thread.id"
                                class="hc-prospect-interaction-cloudtalk-thread"
                            >
                                <div class="hc-prospect-interaction-cloudtalk-thread-title">
                                    <span
                                        class="hc-prospect-interaction-cloudtalk-thread-color"
                                        :style="{
                                            color: thread.color,
                                            backgroundColor: thread.bgcolor,
                                        }"
                                    ></span>
                                    <span v-text="thread.name"></span>
                                    <small
                                        v-text="
                                            thread.user_messages_count +
                                            '/' +
                                            thread.messages_count
                                        "
                                    ></small>
                                </div>

                                <div
                                    v-for="message in cloudtalkMessagesForThread(
                                        thread
                                    )"
                                    :key="message.id"
                                    class="hc-prospect-interaction-cloudtalk-message"
                                >
                                    <div class="hc-prospect-interaction-cloudtalk-message-meta">
                                        <span
                                            v-text="
                                                message.creator
                                                    ? message.creator.name
                                                    : ''
                                            "
                                        ></span>
                                        <span
                                            v-text="
                                                formatCloudtalkDate(
                                                    message.created_at
                                                )
                                            "
                                        ></span>
                                    </div>
                                    <div
                                        class="hc-prospect-interaction-cloudtalk-message-body"
                                        v-text="messagePreview(message.body)"
                                    ></div>
                                    <div
                                        v-if="
                                            message.users &&
                                            message.users.length
                                        "
                                        class="hc-prospect-interaction-cloudtalk-message-users"
                                    >
                                        <icon class="fa fa-user" />
                                        <span
                                            v-text="
                                                message.users
                                                    .map((user) => user.name)
                                                    .join(', ')
                                            "
                                        ></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </aside>
            </div>

            <tab-layout :count="2" :tab="tab" class="hc-flex-1">
                <template #1 v-if="interactionProspect">
                    <item-list style="height: 100%; overflow: auto" padding="12px">
                        <template v-if="can('all.prospect.interaction.add')">
                            <item
                                v-if="interactionProspect.phone_number"
                                @click.prevent="(tab = 1), (frameTab = 3)"
                            >
                                <icon class="fa fa-phone" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t('prospect.interaction.edit_phone_number')
                                    "
                                ></div>
                                <icon class="fa fa-caret-right" />
                            </item>
                            <item
                                v-if="interactionProspect.mobile_phone_number"
                                @click.prevent="(tab = 1), (frameTab = 4)"
                            >
                                <icon class="fa fa-mobile" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t(
                                            'prospect.interaction.edit_mobile_phone_number'
                                        )
                                    "
                                ></div>
                                <icon class="fa fa-caret-right" />
                            </item>
                            <template
                                v-for="number in [
                                    interactionProspect.phone_number,
                                    interactionProspect.mobile_phone_number,
                                ].filter((n) => n)"
                                :key="number"
                            >
                                <!-- Telephone -->
                                <item
                                    tag="a"
                                    class="hc-prospect-interaction-item"
                                    @click="interactionViaTelephone(number)"
                                    :href="'tel:' + number"
                                >
                                    <icon class="fa fa-phone" color="#489f1f" />
                                    <div
                                        class="hc-item-main-content hc-flex-column"
                                    >
                                        <span
                                            v-text="
                                                $t(
                                                    'prospect.interaction.call_by_phone'
                                                )
                                            "
                                        ></span>
                                        <span
                                            class="hc-prospect-interaction-item-number"
                                            v-text="number"
                                        ></span>
                                    </div>
                                </item>

                                <!-- Aircall -->
                                <item
                                    class="hc-prospect-interaction-item"
                                    @click="interactionViaAircall(number)"
                                >
                                    <icon>
                                        <svg viewBox="0 0 40 40">
                                            <path
                                                fill="#00B388"
                                                d="M39.1,9.8c-0.9-4.5-4.5-8-9-9C27.9,0.3,24.2,0,20,0S12.1,0.3,9.9,0.8c-4.5,0.9-8,4.5-9,9 c-0.5,2.3-0.9,6-0.9,10.2c0,4.2,0.3,7.9,0.9,10.2c0.9,4.5,4.5,8,9,9C12.1,39.6,15.8,40,20,40s7.9-0.3,10.1-0.9c4.5-0.9,8-4.5,9-9 c0.5-2.3,0.9-6,0.9-10.2C39.9,15.8,39.6,12.1,39.1,9.8z M29.3,30.5C29.3,30.5,29.3,30.5,29.3,30.5c-0.7,0.3-1.9,0.5-3.5,0.6 c-0.1,0-0.1,0-0.2,0c-0.3,0-0.6-0.2-0.8-0.5c-0.4-0.9-1.2-1.6-2.2-1.8c-0.6-0.1-1.5-0.2-2.6-0.2s-2,0.1-2.6,0.2 c-1,0.2-1.8,0.9-2.2,1.8c-0.1,0.3-0.4,0.5-0.8,0.5c-0.1,0-0.2,0-0.2,0c-1.6-0.2-2.8-0.4-3.5-0.6c0,0,0,0,0,0 c-0.5-0.2-0.8-0.6-0.8-1.2c0,0,0,0,0,0c0,0,0,0,0-0.1c0,0,0,0,0,0c0,0,0,0,0,0c0.1-1.6,1.1-5.5,2.6-9.8c1.7-5,3.5-9,4.2-9.8 c0.1-0.1,0.3-0.2,0.4-0.3c0.1,0,0.1-0.1,0.2-0.1c0,0,0,0,0,0c0.5-0.2,1.5-0.3,2.6-0.3c1.1,0,2.1,0.1,2.6,0.3c0,0,0,0,0,0 c0.1,0,0.2,0.1,0.2,0.1c0.2,0.1,0.3,0.2,0.4,0.3c0,0,0,0,0,0c0.8,0.8,2.6,4.8,4.2,9.8c1.5,4.4,2.5,8.2,2.6,9.8c0,0,0,0,0,0 c0,0,0,0,0,0c0,0,0,0,0,0.1c0,0,0,0,0,0C30.1,29.8,29.8,30.3,29.3,30.5z"
                                            ></path>
                                        </svg>
                                    </icon>
                                    <div
                                        class="hc-item-main-content hc-flex-column"
                                    >
                                        <span
                                            v-text="
                                                $t(
                                                    'prospect.interaction.call_by_aircall'
                                                )
                                            "
                                        ></span>
                                        <span
                                            class="hc-prospect-interaction-item-number"
                                            v-text="number"
                                        ></span>
                                    </div>
                                    <icon class="fa fa-caret-right" />
                                </item>

                                <!-- Ringover -->
                                <item
                                    class="hc-prospect-interaction-item"
                                    @click="interactionViaRingover(number)"
                                >
                                    <icon>
                                        <svg viewBox="0 0 40 40">
                                            <path
                                                d="M9.9,16.9c1.3-4.3,5.3-7.4,10.1-7.4s8.7,3.1,10.1,7.4h9.7C38.2,7.3,30,0,20,0S1.8,7.3,0.3,16.9H9.9z"
                                                style="fill: rgb(85, 195, 192)"
                                            ></path>
                                            <path
                                                d="M30.1,23.1c-1.3,4.3-5.3,7.4-10.1,7.4s-8.7-3.1-10.1-7.4H0.3C1.8,32.7,10,40,20,40s18.2-7.3,19.7-16.9H30.1z"
                                                style="fill: rgb(85, 195, 192)"
                                            ></path>
                                        </svg>
                                    </icon>
                                    <div
                                        class="hc-item-main-content hc-flex-column"
                                    >
                                        <span
                                            v-text="
                                                $t(
                                                    'prospect.interaction.call_by_ringover'
                                                )
                                            "
                                        ></span>
                                        <span
                                            class="hc-prospect-interaction-item-number"
                                            v-text="number"
                                        ></span>
                                    </div>
                                    <icon class="fa fa-caret-right" />
                                </item>

                                <!-- CloudTalk -->
                                <item
                                    class="hc-prospect-interaction-item"
                                    @click="interactionViaCloudtalk(number)"
                                >
                                    <icon>
                                        <svg viewBox="0 0 40 40">
                                            <path
                                                fill="#1f6feb"
                                                d="M30.8,18.6c-0.6-1.4-1.9-2.3-3.4-2.3c-0.2,0-0.4,0-0.6,0.1C26.2,12.5,22.6,9.6,18.3,9.6c-4.9,0-9,3.8-9.5,8.6c-0.2,0-0.4-0.1-0.6-0.1c-3.1,0-5.6,2.5-5.6,5.6c0,3.1,2.5,5.6,5.6,5.6h20.6c2.9,0,5.2-2.3,5.2-5.2C34.1,21.4,32.8,19.4,30.8,18.6z"
                                            ></path>
                                            <path
                                                fill="#ffffff"
                                                d="M22.8,25.9c-1.6-0.8-2.9-2.1-3.7-3.7c-0.1-0.2-0.1-0.5,0.1-0.7l0.7-0.7c0.3-0.3,0.3-0.8,0.1-1.1l-1.1-1.5c-0.2-0.3-0.6-0.4-0.9-0.3c-1.1,0.4-1.9,1.3-2.1,2.4c-0.2,1.4,0.3,2.9,1.2,4.2c1,1.5,2.6,2.6,4.3,3.1c1,0.3,2.1,0.1,2.9-0.5c0.4-0.3,0.5-0.8,0.3-1.2l-0.9-1.6C23.6,26,23.2,25.8,22.8,25.9z"
                                            ></path>
                                        </svg>
                                    </icon>
                                    <div
                                        class="hc-item-main-content hc-flex-column"
                                    >
                                        <span
                                            v-text="
                                                $t(
                                                    'prospect.interaction.call_by_cloudtalk'
                                                )
                                            "
                                        ></span>
                                        <span
                                            class="hc-prospect-interaction-item-number"
                                            v-text="number"
                                        ></span>
                                    </div>
                                    <icon class="fa fa-caret-right" />
                                </item>
                            </template>

                            <!-- Add history -->
                            <item
                                tag="a"
                                class="hc-prospect-interaction-item"
                                @click="addHistory()"
                            >
                                <icon class="fa fa-plus icon-green" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="$t('prospect.interaction.add_history')"
                                ></div>
                                <loading :loading="addingHistory" />
                            </item>
                        </template>
                        <item
                            style="
                                background-color: #7939b8;
                                color: white;
                                margin-top: 10px;
                                margin-bottom: 10px;
                            "
                            v-if="prospectInteractions.length > 0"
                        >
                            <icon class="fa fa-clock" color="white"></icon>
                            <div
                                class="hc-main-content"
                                v-text="$t('prospect.interaction.history')"
                            ></div>
                        </item>
                        <interaction-row
                            v-for="c in prospectInteractions"
                            :key="c.id"
                            :interaction="c"
                        />
                    </item-list>
                </template>

                <!-- List of interaction -->
                <template #2>
                    <frame-layout :count="6" :tab="frameTab" class="hc-flex-1">
                        <template #1 v-if="interactionProspect">
                            <tab-layout
                                :count="2"
                                :tab="aircallTab"
                                class="hc-flex-1"
                            >
                                <template #1>
                                    <div
                                        class="hc-flex-column"
                                        style="height: 100%"
                                    >
                                        <item @click="tab = 0" class="bordered">
                                            <icon class="fa fa-caret-left" />
                                            <div
                                                class="hc-item-main-content"
                                                v-text="
                                                    $t(
                                                        'prospect.interaction.call_by_aircall'
                                                    )
                                                "
                                            ></div>
                                            <icon
                                                class="fa fa-cog"
                                                @click.stop="aircallTab = 1"
                                            />
                                        </item>
                                        <div
                                            style="
                                                flex: 1;
                                                width: 100%;
                                                height: 100%;
                                                padding: 10px;
                                                overflow: auto;
                                            "
                                        >
                                            <aircall
                                                id="aircall-phone"
                                                :number="interaction.number"
                                                :style="{
                                                    width: '100%',
                                                    height: '100%',
                                                    display:
                                                        tab == 1 && frameTab == 0
                                                            ? 'block'
                                                            : 'none',
                                                }"
                                                @outgoing-call="
                                                    (callInfos) => {
                                                        interaction.status =
                                                            'initiated';
                                                        interaction.data = {
                                                            id: callInfos.call_id,
                                                        };
                                                        updateInteraction();
                                                    }
                                                "
                                                @call-ended="
                                                    (callInfos) => {
                                                        interaction.status =
                                                            'ended';
                                                        interaction.data = {
                                                            id: callInfos.call_id,
                                                        };
                                                        updateInteraction();
                                                        nextInteraction();
                                                    }
                                                "
                                                @answered-call="
                                                    (interaction.status =
                                                        'answered'),
                                                        updateInteraction()
                                                "
                                            />
                                        </div>
                                    </div>
                                </template>
                                <template #2>
                                    <div
                                        class="hc-flex-column"
                                        style="height: 100%"
                                    >
                                        <item
                                            @click="aircallTab = 0"
                                            class="bordered"
                                        >
                                            <icon class="fa fa-caret-left" />
                                            <div
                                                class="hc-item-main-content"
                                                v-text="'Paramètre Aircall Webhook'"
                                            ></div>
                                        </item>
                                        <item-list
                                            class="hc-flex-1"
                                            gap="5px"
                                            style="overflow: auto"
                                        >
                                            <item
                                                tag="a"
                                                href="https://dashboard.aircall.io/integrations/flow/install/webhook/webhook/0"
                                                target="_blank"
                                            >
                                                <icon class="fa fa-wifi" />
                                                <div
                                                    class="hc-item-main-content"
                                                    v-text="
                                                        'Rendez-vous sur la page webhook d\'aircall'
                                                    "
                                                ></div>
                                                <icon class="fa fa-caret-right" />
                                            </item>
                                            <item
                                                tag="a"
                                                @click.prevent="
                                                    copyAircallWebhookURLToClipboard
                                                "
                                            >
                                                <icon class="fa fa-link" />
                                                <div
                                                    class="hc-item-main-content"
                                                    v-text="
                                                        'Mettre ' +
                                                        aircallWebhookURL +
                                                        ' comme URL'
                                                    "
                                                ></div>
                                                <icon class="fa fa-copy" />
                                            </item>
                                            <item>
                                                <icon class="fa fa-check" />
                                                <div
                                                    class="hc-item-main-content"
                                                    v-text="
                                                        'Cocher &quot;call.ended&quot; dans la section Appel'
                                                    "
                                                ></div>
                                            </item>
                                            <item>
                                                <icon class="fa fa-check" />
                                                <div
                                                    class="hc-item-main-content"
                                                    v-text="
                                                        'Enfin cliquer sur &quot;Ajouter webhook&quot;'
                                                    "
                                                ></div>
                                            </item>
                                        </item-list>
                                    </div>
                                </template>
                            </tab-layout>
                        </template>

                        <template #2 v-if="interactionProspect">
                            <div class="hc-flex-column" style="height: 100%">
                                <item @click="tab = 0" class="bordered">
                                    <icon class="fa fa-caret-left" />
                                    <div
                                        class="hc-item-main-content"
                                        v-text="
                                            $t(
                                                'prospect.interaction.call_by_ringover'
                                            )
                                        "
                                    ></div>
                                    <icon
                                        class="fa fa-cog"
                                        @click.stop="ringoverSetting"
                                    />
                                </item>
                                <div
                                    style="
                                        flex: 1;
                                        width: 100%;
                                        height: 100%;
                                        overflow: auto;
                                    "
                                >
                                    <ringover
                                        id="ringover-phone"
                                        :number="interaction.number"
                                        tab="phone"
                                        style="flex: 1; width: 100%; height: 100%"
                                        @ringing-call="
                                            (callInfo) => {
                                                interaction.from_number =
                                                    callInfo.data.from;
                                                interaction.status = 'ringing';
                                                interaction.data.id =
                                                    callInfo.data.call_id;
                                                updateInteraction();
                                            }
                                        "
                                        @hangup-call="
                                            (callInfo) => {
                                                interaction.status = 'hangup';
                                                interaction.data.id =
                                                    callInfo.data.call_id;
                                                updateInteraction();
                                                nextInteraction();
                                            }
                                        "
                                        @answered-call="
                                            (interaction.status = 'answered'),
                                                updateInteraction()
                                        "
                                    />
                                </div>
                            </div>
                        </template>

                        <template #3>
                            <select-prospect
                                @back="tab = 0"
                                @prospect-selected="setInteractionProspect"
                            />
                        </template>

                        <template #4>
                            <form
                                class="hc-flex-column"
                                style="height: 100%"
                                @submit.prevent="updateProspectPhoneNumber"
                            >
                                <item @click="tab = 0" class="bordered">
                                    <icon class="fa fa-caret-left" />
                                    <div
                                        class="hc-item-main-content"
                                        v-text="
                                            $t(
                                                'prospect.interaction.edit_phone_number'
                                            )
                                        "
                                    ></div>
                                </item>
                                <item-list padding="12px" style="height: auto">
                                    <v-field
                                        :label="$t('field.prospect.phone_number')"
                                        ><input
                                            type="tel"
                                            v-model.lazy="phoneNumber"
                                    /></v-field>
                                </item-list>
                                <buttons>
                                    <button v-text="$t('update')"></button>
                                </buttons>
                                <loading :loading="updatingPhoneNumber" />
                            </form>
                        </template>

                        <template #5>
                            <form
                                class="hc-flex-column"
                                style="height: 100%; position: relative"
                                @submit.prevent="updateProspectMobilePhoneNumber"
                            >
                                <item @click="tab = 0" class="bordered">
                                    <icon class="fa fa-caret-left" />
                                    <div
                                        class="hc-item-main-content"
                                        v-text="
                                            $t(
                                                'prospect.interaction.edit_mobile_phone_number'
                                            )
                                        "
                                    ></div>
                                </item>
                                <item-list padding="12px" style="height: auto">
                                    <v-field
                                        :label="$t('field.prospect.phone_number')"
                                        ><input
                                            type="tel"
                                            v-model.lazy="mobilePhoneNumber"
                                    /></v-field>
                                </item-list>
                                <buttons>
                                    <button v-text="$t('update')"></button>
                                </buttons>
                                <loading :loading="updatingMobilePhoneNumber" />
                            </form>
                        </template>

                        <template #6 v-if="interactionProspect">
                            <div class="hc-flex-column" style="height: 100%">
                                <item @click="backFromCloudtalk" class="bordered">
                                    <icon class="fa fa-caret-left" />
                                    <div
                                        class="hc-item-main-content"
                                        v-text="
                                            $t(
                                                'prospect.interaction.call_by_cloudtalk'
                                            )
                                        "
                                    ></div>
                                </item>
                                <div
                                    style="
                                        flex: 1;
                                        width: 100%;
                                        height: 100%;
                                        overflow: auto;
                                        position: relative;
                                    "
                                ></div>
                            </div>
                        </template>
                    </frame-layout>
                </template>
            </tab-layout>
        </div>
    </slide>
</template>

<style>
.hc-prospect-interaction-root {
    position: relative;
    width: 100%;
    height: 100%;
    overflow-y: hidden;
}

.hc-prospect-interaction-item {
    padding: 4px 0 !important;
    text-decoration: none;
}
.hc-prospect-interaction-item-number {
    font-size: 11px;
    color: #999999;
}

.hc-prospect-interaction-cloudtalk-panel {
    position: absolute;
    top: 42px;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 5;
    display: flex;
    background: #ffffff;
    opacity: 0;
    pointer-events: none;
    visibility: hidden;
}

.hc-prospect-interaction-cloudtalk-panel.visible {
    opacity: 1;
    pointer-events: auto;
    visibility: visible;
}

.hc-prospect-interaction-cloudtalk-phone {
    flex: 1;
    min-width: 0;
}

.hc-prospect-interaction-cloudtalk-context {
    display: flex;
    flex: 0 0 320px;
    flex-direction: column;
    gap: 14px;
    height: 100%;
    overflow: auto;
    padding: 14px;
    border-left: 1px solid #e5e5e5;
    background: #fafafa;
}

.hc-prospect-interaction-cloudtalk-context-loading {
    position: relative;
    min-height: 80px;
}

.hc-prospect-interaction-cloudtalk-context-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
}

.hc-prospect-interaction-cloudtalk-context-title {
    font-size: 16px;
    font-weight: 700;
    line-height: 1.2;
    color: #222222;
    overflow-wrap: anywhere;
}

.hc-prospect-interaction-cloudtalk-context-subtitle {
    margin-top: 3px;
    font-size: 12px;
    color: #777777;
    overflow-wrap: anywhere;
}

.hc-prospect-interaction-cloudtalk-context-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    color: #555555;
    text-decoration: none;
}

.hc-prospect-interaction-cloudtalk-context-section {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.hc-prospect-interaction-cloudtalk-context-heading {
    font-size: 11px;
    font-weight: 700;
    color: #777777;
    text-transform: uppercase;
}

.hc-prospect-interaction-cloudtalk-context-line {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
    font-size: 12px;
    color: #333333;
}

.hc-prospect-interaction-cloudtalk-context-line span {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.hc-prospect-interaction-cloudtalk-context-empty {
    font-size: 12px;
    color: #999999;
}

.hc-prospect-interaction-cloudtalk-thread {
    padding: 10px;
    border: 1px solid #e7e7e7;
    border-radius: 6px;
    background: #ffffff;
}

.hc-prospect-interaction-cloudtalk-thread-title {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
    font-size: 13px;
    font-weight: 700;
    color: #222222;
}

.hc-prospect-interaction-cloudtalk-thread-title > span:nth-child(2) {
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.hc-prospect-interaction-cloudtalk-thread-title small {
    font-size: 11px;
    font-weight: 600;
    color: #777777;
}

.hc-prospect-interaction-cloudtalk-thread-color {
    display: inline-block;
    flex: 0 0 10px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.hc-prospect-interaction-cloudtalk-message {
    margin-top: 9px;
    padding-top: 9px;
    border-top: 1px solid #eeeeee;
}

.hc-prospect-interaction-cloudtalk-message-meta {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    font-size: 10px;
    color: #999999;
}

.hc-prospect-interaction-cloudtalk-message-body {
    margin-top: 4px;
    display: -webkit-box;
    overflow: hidden;
    color: #333333;
    font-size: 12px;
    line-height: 1.35;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
}

.hc-prospect-interaction-cloudtalk-message-users {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 5px;
    font-size: 11px;
    color: #777777;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import ProspectService from "@/apis/project/prospect";

import { OPEN_MODAL } from "@/actions/modal";
import { SET_PROSPECT, UPDATE_PROSPECT } from "@/actions/project/prospect";
import { SET_INTERACTION_PROSPECT } from "@/actions/project/prospect/interaction";
import { OPEN_LEFT_SLIDE } from "@/actions/slide";
import {
    FETCH_PROSPECT_INTERACTIONS,
    ADD_PROSPECT_INTERACTION,
    UPDATE_PROSPECT_INTERACTION,
    SET_PROSPECT_INTERACTION_TAB,
    SET_PROSPECT_INTERACTION_FRAME_TAB,
} from "@/actions/project/prospect/interaction";
import {
    MAKE_CLOUDTALK_CALL,
    LOOKUP_CLOUDTALK_CALL,
} from "@/actions/project/line";

// Components
import Ringover from "@/components/utils/Ringover.vue";
import Aircall from "@/components/utils/Aircall.vue";
import Cloudtalk from "@/components/utils/Cloudtalk.vue";
import InteractionRow from "./InteractionRow.vue";
import SelectProspect from "../select/Select.vue";

export default {
    components: {
        Ringover,
        Aircall,
        Cloudtalk,
        InteractionRow,
        SelectProspect,
    },

    data() {
        return {
            name: "prospect-manage-interactions",
            tab: 0,
            frameTab: 0,
            aircallTab: 0,
            currentProspectIndex: 0,
            selectedProspects: [],
            phoneNumber: null,
            mobilePhoneNumber: null,
            interaction: this.newInteraction(),
            addingHistory: false,
            fetchingInteraction: false,
            updatingPhoneNumber: false,
            updatingMobilePhoneNumber: false,
            callingCloudtalk: false,
            cloudtalkWaitingForEvent: false,
            cloudtalkLoginRequired: false,
            cloudtalkPhoneVisible: false,
            cloudtalkEventTimeout: null,
            cloudtalkLookup: {
                number: null,
                prospect: null,
                threads: [],
                messages: [],
            },
            cloudtalkLookupLoading: false,
            cloudtalkLookupRequest: 0,
        };
    },

    created() {
        store.commit(SET_PROSPECT_INTERACTION_TAB, 0);
        store.commit(SET_PROSPECT_INTERACTION_FRAME_TAB, 0);
    },

    beforeUnmount() {
        this.clearCloudtalkEventTimeout();
    },

    methods: {
        newInteraction() {
            return {
                source: "telephone",
                number: "",
                status: "new",
                data: {},
            };
        },

        async fetchInteractions() {
            if (this.interactionProspect) {
                this.phoneNumber = this.interactionProspect.phone_number;
                this.mobilePhoneNumber =
                    this.interactionProspect.mobile_phone_number;

                this.fetchingInteraction = true;

                try {
                    await store.dispatch(FETCH_PROSPECT_INTERACTIONS);
                } finally {
                    this.fetchingInteraction = false;
                }
            } else if (this.prospectsSelected.length == 0) {
                this.tab = 1;
                this.frameTab = 2;
            }
        },

        addHistory() {
            this.addingHistory = true;
            this.interaction = this.newInteraction();
            this.interaction.source = "telephone";
            this.interaction.number = "";
            this.interaction.from_number = this.user.mobile_phone_number
                ? this.user.mobile_phone_number
                : this.user.phone_number
                ? this.user.phone_number
                : "";
            this.addInteraction().finally(() => {
                this.addingHistory = false;
            });
        },

        interactionViaTelephone(number) {
            this.interaction = this.newInteraction();
            this.interaction.source = "telephone";
            this.interaction.number = number;
            this.addInteraction();
        },

        interactionViaAircall(number) {
            this.tab = 1;
            this.frameTab = 0;
            this.interaction = this.newInteraction();
            this.interaction.source = "aircall";
            this.interaction.number = number;
            this.addInteraction();
        },

        interactionViaRingover(number) {
            this.tab = 1;
            this.frameTab = 1;
            this.interaction = this.newInteraction();
            this.interaction.source = "ringover";
            this.interaction.number = number;
            this.addInteraction();
        },

        async interactionViaCloudtalk(number) {
            this.tab = 1;
            this.frameTab = 5;
            this.cloudtalkPhoneVisible = true;
            this.cloudtalkWaitingForEvent = false;
            this.cloudtalkLoginRequired = false;
            this.interaction = this.newInteraction();
            this.interaction.source = "cloudtalk";
            this.interaction.number = number;
            await this.addInteraction();
            this.fetchCloudtalkCallContext({ external_number: number });
            this.makeCloudtalkCall();
        },

        async makeCloudtalkCall() {
            if (this.callingCloudtalk || !this.interaction.number) {
                return;
            }

            this.callingCloudtalk = true;
            this.cloudtalkPhoneVisible = true;
            this.cloudtalkWaitingForEvent = true;
            this.cloudtalkLoginRequired = false;
            this.clearCloudtalkEventTimeout();

            try {
                const params = {
                    number: this.interaction.number,
                };

                if (this.cloudtalkLine) {
                    params.line_id = this.cloudtalkLine.id;
                }

                const response = await store.dispatch(
                    MAKE_CLOUDTALK_CALL,
                    params
                );

                this.interaction.status = "initiated";
                this.interaction.data = {
                    ...(this.interaction.data || {}),
                    cloudtalk: response.responseData,
                };
                this.updateInteraction();
                if (this.cloudtalkWaitingForEvent) {
                    this.startCloudtalkEventTimeout();
                }
            } catch (error) {
                this.cloudtalkWaitingForEvent = false;
                this.clearCloudtalkEventTimeout();

                const status =
                    error.response &&
                    error.response.data &&
                    error.response.data.status
                        ? error.response.data.status
                        : error.response
                        ? error.response.status
                        : null;

                const message =
                    error.response &&
                    error.response.data &&
                    error.response.data.message
                        ? error.response.data.message
                        : "Impossible de lancer l'appel CloudTalk.";

                if (status == 403) {
                    this.showCloudtalkLogin();
                }

                this.interaction.status = "failed";
                this.interaction.data = {
                    ...(this.interaction.data || {}),
                    error: message,
                };
                this.updateInteraction();

                flashError({
                    title: "CloudTalk",
                    body: message,
                    duration: 7000,
                });
            } finally {
                this.callingCloudtalk = false;
            }
        },

        showCloudtalkPhone() {
            this.clearCloudtalkEventTimeout();
            this.cloudtalkWaitingForEvent = false;
            this.cloudtalkLoginRequired = false;
            this.cloudtalkPhoneVisible = true;
            this.tab = 1;
            this.frameTab = 5;
        },

        displayCloudtalkPhoneFromIframe(payload = {}) {
            this.prepareCloudtalkInteractionFromIframe(payload.properties || {});

            if (!this.leftSlideOpen(this.name)) {
                store.commit(OPEN_LEFT_SLIDE, this.name);
            }

            this.showCloudtalkPhone();

            setTimeout(() => {
                this.showCloudtalkPhone();
            }, 0);
        },

        prepareCloudtalkInteractionFromIframe(callInfos = {}) {
            const callId =
                callInfos.call_uuid || callInfos.call_id || callInfos.id;
            const currentCallId =
                this.interaction &&
                this.interaction.data &&
                (this.interaction.data.id ||
                    this.interaction.data.call_uuid ||
                    this.interaction.data.call_id);

            if (
                this.interaction &&
                this.interaction.source == "cloudtalk" &&
                (!callId || !currentCallId || currentCallId == callId)
            ) {
                this.fetchCloudtalkCallContext(callInfos);
                return;
            }

            this.interaction = this.newInteraction();
            this.interaction.source = "cloudtalk";
            this.interaction.number = this.cloudtalkCallNumber(callInfos);
            this.interaction.from_number = callInfos.internal_number || "";
            this.setCloudtalkCallData(callInfos);
            this.fetchCloudtalkCallContext(callInfos);
        },

        showCloudtalkLogin() {
            this.cloudtalkWaitingForEvent = false;
            this.cloudtalkLoginRequired = true;
            this.cloudtalkPhoneVisible = false;
            this.tab = 1;
            this.frameTab = 5;
        },

        backFromCloudtalk() {
            this.resetCloudtalkSlideState();
        },

        resetCloudtalkSlideState() {
            if (this.interaction.source != "cloudtalk" && this.frameTab != 5) {
                return;
            }

            this.cloudtalkPhoneVisible = false;
            this.cloudtalkWaitingForEvent = false;
            this.cloudtalkLoginRequired = false;
            this.clearCloudtalkEventTimeout();
            this.tab = 0;
            this.frameTab = 0;
        },

        startCloudtalkEventTimeout() {
            this.clearCloudtalkEventTimeout();

            this.cloudtalkEventTimeout = setTimeout(() => {
                if (!this.cloudtalkWaitingForEvent) {
                    return;
                }

                this.cloudtalkWaitingForEvent = false;
                this.showCloudtalkLogin();

                flashWarning({
                    title: "CloudTalk",
                    body: "Aucun evenement CloudTalk recu. Connectez-vous dans CloudTalk puis relancez l'appel.",
                    duration: 7000,
                });
            }, 25000);
        },

        clearCloudtalkEventTimeout() {
            if (this.cloudtalkEventTimeout) {
                clearTimeout(this.cloudtalkEventTimeout);
                this.cloudtalkEventTimeout = null;
            }
        },

        cloudtalkCallRinging(callInfos) {
            this.showCloudtalkPhone();
            this.interaction.status = "ringing";
            this.setCloudtalkCallData(callInfos);
            this.fetchCloudtalkCallContext(callInfos);
            this.updateInteraction();
        },

        cloudtalkCallOutgoing(callInfos) {
            this.showCloudtalkPhone();
            this.interaction.status = "initiated";
            this.setCloudtalkCallData(callInfos);
            this.fetchCloudtalkCallContext(callInfos);
            this.updateInteraction();
        },

        cloudtalkCallAnswered(callInfos) {
            this.showCloudtalkPhone();
            this.interaction.status = "answered";
            this.setCloudtalkCallData(callInfos);
            this.fetchCloudtalkCallContext(callInfos);
            this.updateInteraction();
        },

        cloudtalkCallHangup(callInfos) {
            this.showCloudtalkPhone();
            this.interaction.status = "hangup";
            this.setCloudtalkCallData(callInfos);
            this.fetchCloudtalkCallContext(callInfos);
            this.updateInteraction();
            if (this.interactionProspect) {
                this.nextInteraction();
            }
        },

        cloudtalkCallEnded(callInfos) {
            this.showCloudtalkPhone();
            this.interaction.status = "ended";
            this.setCloudtalkCallData(callInfos);
            this.fetchCloudtalkCallContext(callInfos);
            this.updateInteraction();
            if (this.interactionProspect) {
                this.nextInteraction();
            }
        },

        cloudtalkCallContactInfo(callInfos) {
            this.fetchCloudtalkCallContext(callInfos);
        },

        setCloudtalkCallData(callInfos = {}) {
            const callId = this.cloudtalkCallId(callInfos);
            const number = this.cloudtalkCallNumber(callInfos, true);

            if (number) {
                this.interaction.number = number;
            }

            this.interaction.data = {
                ...(this.interaction.data || {}),
                ...(callId ? { id: callId } : {}),
                ...(number ? { external_number: number } : {}),
                ...(callInfos.direction
                    ? { direction: callInfos.direction }
                    : {}),
            };
        },

        cloudtalkCallId(callInfos = {}, useFallback = false) {
            return (
                callInfos.call_uuid ||
                callInfos.call_id ||
                callInfos.id ||
                (useFallback &&
                this.interaction &&
                this.interaction.data &&
                this.interaction.data.id
                    ? this.interaction.data.id
                    : "") ||
                ""
            );
        },

        cloudtalkCallNumber(callInfos = {}, useFallback = false) {
            return (
                callInfos.external_number ||
                callInfos.customer_number ||
                callInfos.contact_phone ||
                callInfos.phone_number ||
                callInfos.number ||
                callInfos.from ||
                callInfos.to ||
                (useFallback && this.cloudtalkLookup.number
                    ? this.cloudtalkLookup.number
                    : "") ||
                (useFallback && this.interaction.number
                    ? this.interaction.number
                    : "") ||
                ""
            );
        },

        async fetchCloudtalkCallContext(callInfos = {}) {
            const number = this.cloudtalkCallNumber(callInfos, true);
            const numberKey = this.normalizePhone(number);
            const callId = this.cloudtalkCallId(callInfos, true);
            const previousLookup = { ...(this.cloudtalkLookup || {}) };
            const sameResolvedCall =
                previousLookup.prospect &&
                previousLookup.callId &&
                callId &&
                previousLookup.callId == callId;

            if (!numberKey || numberKey.length < 6) {
                return;
            }

            if (
                this.cloudtalkLookup.numberKey == numberKey &&
                this.cloudtalkLookup.resolved
            ) {
                return;
            }

            const request = ++this.cloudtalkLookupRequest;
            this.cloudtalkLookupLoading = true;
            this.cloudtalkLookup = {
                ...this.cloudtalkLookup,
                number,
                numberKey,
                callId,
                resolved: false,
            };

            try {
                const data = await store.dispatch(LOOKUP_CLOUDTALK_CALL, {
                    number,
                });

                if (request != this.cloudtalkLookupRequest) {
                    return;
                }

                if (!data.prospect && sameResolvedCall) {
                    this.cloudtalkLookup = {
                        ...previousLookup,
                        number: previousLookup.number || data.number || number,
                        numberKey: previousLookup.numberKey || numberKey,
                        callId,
                        resolved: true,
                    };
                    return;
                }

                this.cloudtalkLookup = {
                    number: data.number || number,
                    numberKey,
                    callId,
                    prospect: data.prospect || null,
                    threads: data.threads || [],
                    messages: data.messages || [],
                    resolved: true,
                };

                if (data.prospect) {
                    if (
                        !this.interactionProspect ||
                        this.interactionProspect.id != data.prospect.id
                    ) {
                        store.commit(SET_INTERACTION_PROSPECT, data.prospect);
                    }

                    if (
                        this.interaction &&
                        this.interaction.source == "cloudtalk" &&
                        !this.interaction.id
                    ) {
                        try {
                            await this.addInteraction();
                        } catch (error) {
                            this.interaction.data = {
                                ...(this.interaction.data || {}),
                                error: error.message,
                            };
                        }
                    }
                }
            } catch (error) {
                if (request == this.cloudtalkLookupRequest) {
                    this.cloudtalkLookup = {
                        number,
                        numberKey,
                        callId,
                        prospect: null,
                        threads: [],
                        messages: [],
                        resolved: true,
                    };
                }
            } finally {
                if (request == this.cloudtalkLookupRequest) {
                    this.cloudtalkLookupLoading = false;
                }
            }
        },

        normalizePhone(number) {
            return String(number || "").replace(/\D+/g, "");
        },

        cloudtalkMessagesForThread(thread) {
            return this.cloudtalkLookupMessages.filter(
                (message) => message.thread_id == thread.id
            );
        },

        messagePreview(body) {
            return String(body || "")
                .replace(/<br\s*\/?>/g, " ")
                .replace(/<[^>]+>/g, " ")
                .replace(/\s+/g, " ")
                .trim();
        },

        formatCloudtalkDate(value) {
            if (!value) {
                return "";
            }

            return new Date(value).toLocaleString();
        },

        /**
         *
         */
        async addInteraction() {
            this.addingInteraction = true;

            try {
                this.interaction = await store.dispatch(
                    ADD_PROSPECT_INTERACTION,
                    this.interaction
                );
            } finally {
                this.addingInteraction = false;
            }
        },

        /**
         *
         */
        async updateInteraction() {
            if (!this.interaction || !this.interactionProspect) {
                return;
            }

            if (!this.interaction.id) {
                this.interaction = await store.dispatch(
                    ADD_PROSPECT_INTERACTION,
                    this.interaction
                );
                return;
            }

            store.dispatch(UPDATE_PROSPECT_INTERACTION, this.interaction);
        },

        /**
         *
         */
        nextInteraction() {
            if (
                this.selectedProspects.length - 1 >
                this.this.currentProspectIndex
            ) {
                this.currentProspectIndex++;
            } else {
                store.commit(SET_INTERACTION_PROSPECT, null);
            }
        },

        /**
         *
         */
        setInteractionProspect(prospect) {
            store.commit(SET_PROSPECT, prospect);
            this.tab = 0;
        },

        async updateProspectPhoneNumber() {
            this.updatingPhoneNumber = true;

            try {
                await store.dispatch(UPDATE_PROSPECT, {
                    id: this.interactionProspect.id,
                    phone_number: this.phoneNumber,
                });
            } finally {
                this.updatingPhoneNumber = false;
                this.tab = 0;
            }
        },

        async updateProspectMobilePhoneNumber() {
            this.updatingMobilePhoneNumber = true;

            try {
                await store.dispatch(UPDATE_PROSPECT, {
                    id: this.interactionProspect.id,
                    mobile_phone_number: this.mobilePhoneNumber,
                });
            } finally {
                this.updatingMobilePhoneNumber = false;
                this.tab = 0;
            }
        },

        async fetchSelectedProspects() {
            if (this.prospectsSelected.length == 0) {
                this.selectedProspects = [];
                return;
            }

            try {
                const { data } = await ProspectService.get(this.project.slug, {
                    params: {
                        filters: JSON.stringify({
                            ids: this.prospectsSelected,
                        }),
                        fields: "first_name,last_name,phone_number,mobile_phone_number",
                    },
                });
                this.selectedProspects = data.data.filter(
                    (prospect) =>
                        prospect.phone_number || prospect.mobile_phone_number
                );
            } finally {
                this.fetchingProspect = false;
            }
        },

        /**
         * Copy webhook URL to clipboard
         */
        copyAircallWebhookURLToClipboard() {
            navigator.clipboard.writeText(this.aircallWebhookURL);

            flashInfo({
                title: "Aircall",
                body: "URL Webhook Aircall copié",
                duration: 5000,
            });
        },

        ringoverSetting() {
            store.commit(OPEN_MODAL, "setting-ringover");
        },
    },

    watch: {
        async interactionProspect(newValue, oldValue) {
            if (newValue && this.leftSlideOpen(this.name)) {
                this.fetchInteractions();

                if (
                    newValue.phone_number &&
                    (!oldValue ||
                        oldValue.phone_number == this.interaction.number)
                ) {
                    this.interaction.number = newValue.phone_number;
                } else {
                    this.interaction.number = newValue.mobile_phone_number;
                }
            }
        },

        selectedProspects() {
            this.currentProspectIndex = 0;
        },

        interactionTab() {
            this.tab = this.interactionTab;
        },

        interactionFrameTab() {
            this.frameTab = this.interactionFrameTab;
        },

        currentProspect(newValue) {
            if (newValue) {
                setTimeout(() => {
                    store.commit(SET_INTERACTION_PROSPECT, newValue);
                }, 1000);
            }
        },
    },

    computed: {
        ...mapGetters("auth", ["user"]),
        ...mapGetters([
            "project",
            "interactionProspect",
            "prospectFullName",
            "prospectInteractions",
            "interactionTab",
            "interactionFrameTab",
            "prospectsSelected",
            "leftSlideOpen",
            "can",
            "lines",
        ]),

        currentProspect() {
            if (this.selectedProspects.length > this.currentProspectIndex) {
                return this.selectedProspects[this.currentProspectIndex];
            }

            return null;
        },

        interactionTitleProspect() {
            return this.interactionProspect || this.cloudtalkCallProspect;
        },

        slideWidth() {
            if (this.cloudtalkPhoneDisplayed && this.cloudtalkCallContextVisible) {
                return "760px";
            }

            if (this.tab == 1 && this.frameTab == 0) {
                return "395px";
            }

            return "300px";
        },

        /**
         * Webhook URL
         * for web service import
         */
        aircallWebhookURL: function () {
            return window.location.origin + "/webhook/aircall";
        },

        cloudtalkLoading() {
            return this.callingCloudtalk || this.cloudtalkWaitingForEvent;
        },

        cloudtalkPhoneDisplayed() {
            return (
                (this.cloudtalkPhoneVisible ||
                    this.cloudtalkLoginRequired ||
                    this.cloudtalkLoading) &&
                this.tab == 1 &&
                this.frameTab == 5
            );
        },

        cloudtalkCallContextVisible() {
            return this.cloudtalkLookupLoading || !!this.cloudtalkCallProspect;
        },

        cloudtalkCallProspect() {
            return this.cloudtalkLookup && this.cloudtalkLookup.prospect
                ? this.cloudtalkLookup.prospect
                : null;
        },

        cloudtalkProspectName() {
            if (!this.cloudtalkCallProspect) {
                return "";
            }

            const name = [
                this.cloudtalkCallProspect.first_name,
                this.cloudtalkCallProspect.last_name,
            ]
                .filter((value) => value)
                .join(" ");

            return (
                name ||
                this.cloudtalkCallProspect.company_name ||
                this.cloudtalkCallProspect.email ||
                this.cloudtalkLookup.number ||
                ""
            );
        },

        cloudtalkLookupThreads() {
            return this.cloudtalkLookup && this.cloudtalkLookup.threads
                ? this.cloudtalkLookup.threads
                : [];
        },

        cloudtalkLookupMessages() {
            return this.cloudtalkLookup && this.cloudtalkLookup.messages
                ? this.cloudtalkLookup.messages
                : [];
        },

        cloudtalkLine() {
            return this.lines.find(
                (line) =>
                    line.operator === "cloudtalk" &&
                    this.user &&
                    line.user_id == this.user.id
            );
        },
    },
};
</script>
