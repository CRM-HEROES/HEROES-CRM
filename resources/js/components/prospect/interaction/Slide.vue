<template>
    <slide
        :name="name"
        @open="
            fetchInteractions(), fetchSelectedProspects(), fetchOperatorsConfigStatus()
        "
        :title="
            $t('prospect.interaction.title', {
                prospect: interactionProspect
                    ? interactionProspect.first_name
                    : '',
            })
        "
        :url="
            interactionProspect
                ? {
                      name: 'prospect.show',
                      params: {
                          project: project.slug,
                          prospect: interactionProspect.id,
                      },
                  }
                : null
        "
        :left="true"
        icon="fa fa-phone"
        :style="{
            width:
                tab == 1 && frameTab == 0
                    ? '395px'
                    : tab == 1 && frameTab == 1
                    ? '300px'
                    : '300px',
        }"
    >
        <tab-layout :count="2" :tab="tab" class="hc-flex-1">
            <template #1 v-if="interactionProspect">
                <item-list style="height: 100%; overflow: auto" padding="12px">
                    <template v-if="can('all.prospect.interaction.add')">
                        <item
                            v-if="interactionProspect.phone_number"
                            @click.prevent="(tab = 1), (frameTab = 4)"
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
                            @click.prevent="(tab = 1), (frameTab = 5)"
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
                                <icon
                                    v-if="ringoverConfigured"
                                    class="fa fa-check-circle"
                                    color="#09be0c"
                                    v-tooltip="$t('line.operator.configured')"
                                />
                                <icon class="fa fa-caret-right" />
                            </item>

                            <!-- Kavkom -->
                            <item
                                class="hc-prospect-interaction-item"
                                @click="interactionViaKavkom(number)"
                            >
                                <icon class="fa fa-phone" color="#8e24aa" />
                                <div
                                    class="hc-item-main-content hc-flex-column"
                                >
                                    <span
                                        v-text="
                                            $t(
                                                'prospect.interaction.call_by_kavkom'
                                            )
                                        "
                                    ></span>
                                    <span
                                        class="hc-prospect-interaction-item-number"
                                        v-text="number"
                                    ></span>
                                </div>
                                <icon
                                    v-if="kavkomConfigured"
                                    class="fa fa-check-circle"
                                    color="#09be0c"
                                    v-tooltip="$t('line.operator.configured')"
                                />
                                <icon class="fa fa-caret-right" />
                            </item>

                            <!-- Kavkom avec IA (ai-phone-agent) -->
                            <item
                                class="hc-prospect-interaction-item"
                                @click="interactionViaKavkomAI(number)"
                            >
                                <icon class="fa fa-robot" color="#3f51b5" />
                                <div
                                    class="hc-item-main-content hc-flex-column"
                                >
                                    <span
                                        v-text="
                                            $t(
                                                'prospect.interaction.call_by_kavkom_ai'
                                            )
                                        "
                                    ></span>
                                    <span
                                        class="hc-prospect-interaction-number"
                                        v-text="number"
                                    ></span>
                                </div>
                                <icon
                                    v-if="kavkomConfigured"
                                    class="fa fa-check-circle"
                                    color="#09be0c"
                                    v-tooltip="$t('line.operator.configured')"
                                />
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
                <frame-layout :count="8" :tab="frameTab" class="hc-flex-1">
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
                            </item>
                            <div
                                style="
                                    flex: 1;
                                    width: 100%;
                                    height: 100%;
                                    overflow: auto;
                                    padding: 16px;
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

                    <template #3 v-if="interactionProspect">
                        <div class="hc-flex-column" style="height: 100%">
                            <item @click="tab = 0" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t(
                                            'prospect.interaction.call_by_kavkom'
                                        )
                                    "
                                ></div>
                            </item>
                            <div class="hc-kavkom-call-panel">
                                <div class="hc-kavkom-call-card">
                                    <div class="hc-kavkom-call-card-header">
                                        <span class="hc-kavkom-call-icon">
                                            <icon class="fa fa-phone" />
                                        </span>
                                        <div>
                                            <div class="hc-kavkom-call-label">Appel Kavkom</div>
                                            <div class="hc-kavkom-call-number">
                                                {{ interaction.number }}
                                            </div>
                                        </div>
                                        <span
                                            :class="[
                                                'hc-kavkom-call-ready',
                                                kavkomReady ? 'is-ready' : 'is-loading',
                                            ]"
                                        >
                                            <i class="fa fa-circle"></i>
                                            {{ kavkomReady ? "Prêt" : "Connexion" }}
                                        </span>
                                    </div>

                                    <!--
                                        Panneau d'affichage du softphone
                                        partagé : il ne compose pas lui-même le
                                        numéro de destination, il auto-répond
                                        au leg agent renvoyé par le PBX Kavkom
                                        après l'API REST (triggerKavkomCall) et
                                        affiche l'état de l'enregistrement SIP
                                        et des appels. L'enregistrement et les
                                        événements viennent du widget global
                                        (voir @/utils/kavkom-phone et
                                        KavkomIncomingCall.vue), ce qui permet
                                        aussi de recevoir les appels entrants
                                        quand cet onglet est fermé.
                                    -->
                                    <kavkom
                                        ref="kavkomWebphone"
                                        id="kavkom-webphone"
                                        :project-id="project.id"
                                    />
                                </div>

                                <div
                                    v-if="kavkomCallMessage"
                                    :class="[
                                        'hc-kavkom-call-status',
                                        kavkomCallSuccess ? 'success' : 'error',
                                    ]"
                                >
                                    {{ kavkomCallMessage }}
                                </div>

                                <button
                                    type="button"
                                    class="hc-button-secondary hc-kavkom-call-action"
                                    :disabled="callingViaKavkom || !kavkomReady"
                                    @click="triggerKavkomCall(interaction.number)"
                                >
                                    <i class="fa fa-phone"></i>
                                    {{
                                        callingViaKavkom
                                            ? "Appel en cours..."
                                            : "Appeler"
                                    }}
                                </button>

                            </div>
                        </div>
                    </template>

                    <template #4>
                        <select-prospect
                            @back="tab = 0"
                            @prospect-selected="setInteractionProspect"
                        />
                    </template>

                    <template #5>
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

                    <template #6>
                        <form
                            class="hc-flex-column"
                            style="height: 100%; position"
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

                    <template #7 v-if="interactionProspect">
                        <div class="hc-flex-column" style="height: 100%">
                            <item @click="tab = 0" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t(
                                            'prospect.interaction.call_by_twilio'
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
                                    padding: 16px;
                                "
                            >
                            </div>
                        </div>
                    </template>

                    <!--
                        Appel piloté par l'agent vocal IA (Gemini Live).
                        Le service Node (ai-phone-agent/) n'est pas encore
                        déployé : le panneau prépare l'intégration en
                        appelant l'API Laravel qui le sollicitera.
                    -->
                    <template #8 v-if="interactionProspect">
                        <div class="hc-flex-column" style="height: 100%">
                            <item @click="tab = 0" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t(
                                            'prospect.interaction.call_by_kavkom_ai'
                                        )
                                    "
                                ></div>
                            </item>
                            <div class="hc-kavkom-call-panel">
                                <div
                                    class="hc-kavkom-call-card hc-kavkom-call-card-ai"
                                >
                                    <div class="hc-kavkom-call-card-header">
                                        <span
                                            class="hc-kavkom-call-icon hc-kavkom-call-icon-ai"
                                        >
                                            <icon class="fa fa-robot" />
                                        </span>
                                        <div>
                                            <div class="hc-kavkom-call-label">
                                                Appel Kavkom avec IA
                                            </div>
                                            <div
                                                class="hc-kavkom-call-number"
                                            >
                                                {{ interaction.number }}
                                            </div>
                                        </div>
                                        <span
                                            :class="[
                                                'hc-kavkom-call-ready',
                                                kavkomReady
                                                    ? 'is-ready'
                                                    : 'is-loading',
                                            ]"
                                        >
                                            <i class="fa fa-circle"></i>
                                            {{
                                                kavkomReady
                                                    ? "Prêt"
                                                    : "Connexion"
                                            }}
                                        </span>
                                    </div>

                                    <!--
                                        Même softphone partagé que l'onglet
                                        Kavkom : l'agent IA rejoint une
                                        conférence FreeSWITCH à trois et
                                        fait sonner l'extension de
                                        l'utilisateur, que ce panneau
                                        auto-répond via kavkom-phone.
                                    -->
                                    <kavkom
                                        ref="kavkomWebphoneAI"
                                        id="kavkom-webphone-ai"
                                        :project-id="project.id"
                                    />
                                </div>

                                <div
                                    v-if="aiCallMessage"
                                    :class="[
                                        'hc-kavkom-call-status',
                                        aiCallSuccess ? 'success' : 'error',
                                    ]"
                                >
                                    {{ aiCallMessage }}
                                </div>

                                <!-- Transcription live Gemini -->
                                <div
                                    v-if="aiCallTranscript.length > 0"
                                    ref="aiTranscriptBox"
                                    class="hc-ai-transcript-box"
                                >
                                    <div
                                        v-for="(line, i) in aiCallTranscript"
                                        :key="i"
                                        :class="[
                                            'hc-ai-transcript-line',
                                            line.speaker === 'assistant'
                                                ? 'hc-ai-transcript-line--ai'
                                                : 'hc-ai-transcript-line--caller',
                                        ]"
                                    >
                                        <span class="hc-ai-transcript-speaker">{{
                                            line.speaker === "assistant" ? "IA" : "Prospect"
                                        }}</span>
                                        <span class="hc-ai-transcript-text">{{ line.text }}</span>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="hc-button-secondary hc-kavkom-call-action"
                                    :disabled="
                                        callingViaAIAgent || !kavkomReady
                                    "
                                    @click="
                                        triggerAIAgentCall(interaction.number)
                                    "
                                >
                                    <i class="fa fa-robot"></i>
                                    {{
                                        callingViaAIAgent
                                            ? "Connexion de l'IA..."
                                            : "Appeler avec l'IA"
                                    }}
                                </button>

                                <p class="hc-kavkom-call-help">
                                    L'agent vocal IA (Gemini Live) rejoint
                                    la conférence et échange avec le
                                    prospect. Décrochez votre poste Kavkom
                                    pour participer ou écouter l'appel.
                                </p>
                            </div>
                        </div>
                    </template>
                </frame-layout>
            </template>
        </tab-layout>
    </slide>
</template>

<style>
.hc-prospect-interaction-item {
    padding: 4px 0 !important;
    text-decoration: none;
}
.hc-prospect-interaction-item-number {
    font-size: 11px;
    color: #999999;
}
.hc-kavkom-call-panel {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 14px;
    padding: 18px;
    text-align: center;
    background: linear-gradient(160deg, #faf7ff 0%, #ffffff 55%);
}
.hc-kavkom-call-card {
    width: 100%;
    padding: 16px;
    text-align: left;
    background: #fff;
    border: 1px solid #eadcf7;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(116, 52, 162, 0.08);
}
.hc-kavkom-call-card-header {
    display: flex;
    align-items: center;
    gap: 11px;
    margin-bottom: 14px;
}
.hc-kavkom-call-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    color: #fff;
    background: #8e24aa;
    border-radius: 10px;
}
/* IA : même panneau que Kavkom, avec un accent visuel distinct. */
.hc-kavkom-call-icon.hc-kavkom-call-icon-ai {
    background: #3f51b5;
}
.hc-kavkom-call-card-ai {
    border-color: #d5d9f5;
    box-shadow: 0 8px 20px rgba(63, 81, 181, 0.08);
}
.hc-kavkom-call-label {
    color: #7b7284;
    font-size: 12px;
    font-weight: 600;
}
.hc-kavkom-call-help {
    font-size: 12px;
    color: #6c757d;
    line-height: 1.5;
    max-width: 320px;
}
.hc-kavkom-call-number {
    margin-top: 2px;
    font-size: 18px;
    font-weight: 600;
    color: #343a40;
}
.hc-kavkom-call-ready {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-left: auto;
    padding: 4px 7px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}
.hc-kavkom-call-ready i {
    font-size: 7px;
}
.hc-kavkom-call-ready.is-ready {
    color: #16794a;
    background: #e7f7ef;
}
.hc-kavkom-call-ready.is-loading {
    color: #896b16;
    background: #fff6d8;
}
.hc-kavkom-call-status {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #6c757d;
    pointer-events: none;
}
.hc-kavkom-call-status.success {
    color: #2e7d32;
}
.hc-kavkom-call-status.error {
    color: #c62828;
}
.hc-kavkom-call-panel > .hc-button-secondary {
    width: 100%;
    min-height: 40px;
    color: #fff;
    background: #8e24aa;
    border-color: #8e24aa;
}
.hc-kavkom-call-action {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.hc-kavkom-call-panel > .hc-button-secondary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
/* Boîte de transcription live Gemini */
.hc-ai-transcript-box {
    width: 100%;
    max-height: 200px;
    overflow-y: auto;
    background: #f7f5ff;
    border: 1px solid #e0d6f7;
    border-radius: 8px;
    padding: 10px 12px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    font-size: 12px;
    line-height: 1.5;
    text-align: left;
}
.hc-ai-transcript-line {
    display: flex;
    gap: 6px;
    align-items: flex-start;
}
.hc-ai-transcript-speaker {
    flex-shrink: 0;
    font-weight: 700;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding-top: 2px;
    min-width: 48px;
}
.hc-ai-transcript-line--ai .hc-ai-transcript-speaker {
    color: #3f51b5;
}
.hc-ai-transcript-line--caller .hc-ai-transcript-speaker {
    color: #6c757d;
}
.hc-ai-transcript-text {
    color: #343a40;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import ProspectService from "@/apis/project/prospect";
import ApiService from "@/apis/api.service";

import { SET_PROSPECT, UPDATE_PROSPECT } from "@/actions/project/prospect";
import { SET_INTERACTION_PROSPECT } from "@/actions/project/prospect/interaction";
import {
    FETCH_PROSPECT_INTERACTIONS,
    ADD_PROSPECT_INTERACTION,
    UPDATE_PROSPECT_INTERACTION,
    SET_PROSPECT_INTERACTION_TAB,
    SET_PROSPECT_INTERACTION_FRAME_TAB,
} from "@/actions/project/prospect/interaction";

// Kavkom softphone events (see @/utils/kavkom-phone): the SIP registration
// is shared by the whole session, so the call events are received here even
// when the Kavkom tab of this slide is not open.
import EventBus from "@/utils/event-bus";
import kavkomPhone, { KAVKOM_EVENTS } from "@/utils/kavkom-phone";

// Components
import Ringover from "@/components/utils/Ringover.vue";
import Kavkom from "@/components/utils/Kavkom.vue";
import Aircall from "@/components/utils/Aircall.vue";
import InteractionRow from "./InteractionRow.vue";
import SelectProspect from "../select/Select.vue";

export default {
    components: {
        Ringover,
        Kavkom,
        Aircall,
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
            callingViaKavkom: false,
            kavkomCallMessage: "",
            kavkomCallSuccess: false,
            kavkomCallState: "idle",
            kavkomCallUuid: null,
            kavkomDebugTimer: null,
            kavkomDebugLastStatus: null,
            // Softphone prêt = enregistré en SIP côté navigateur, capable
            // de recevoir/auto-répondre au leg agent envoyé par le PBX.
            kavkomReady: false,
            // Si l'utilisateur clique "Appeler" avant que le softphone ne
            // soit encore enregistré, on mémorise le numéro pour le lancer
            // dès que le softphone devient prêt (onKavkomReady).
            pendingKavkomNumber: "",

            // Appel piloté par l'agent vocal IA (voir ai-phone-agent/) :
            // le bridge monte une conférence FreeSWITCH à trois
            // (poste du conseiller + prospect + agent Gemini Live).
            callingViaAIAgent: false,
            aiCallMessage: "",
            aiCallSuccess: false,
            aiCallUuid: null,
            // Numéro mémorisé si l'appel IA est lancé avant que le
            // softphone Kavkom ne soit enregistré (voir onKavkomReady).
            pendingAINumber: "",

            // Transcription live de l'appel IA : reçue via WebSocket depuis
            // le service ai-phone-agent (port 14002 → TRANSCRIPT_WS_PORT).
            aiCallTranscript: [],
            aiCallWs: null,
            aiCallWsActive: false,
        };
    },

    created() {
        store.commit(SET_PROSPECT_INTERACTION_TAB, 0);
        store.commit(SET_PROSPECT_INTERACTION_FRAME_TAB, 0);
        this.subscribeKavkomEvents();
    },

    beforeDestroy() {
        this.unsubscribeKavkomEvents();
        this.stopKavkomDebugPolling();
        this.closeTranscriptWs();
    },

    beforeUnmount() {
        this.unsubscribeKavkomEvents();
        this.stopKavkomDebugPolling();
        this.closeTranscriptWs();
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
                this.frameTab = 3;
            }
        },

        async fetchOperatorsConfigStatus() {
            // No custom Twilio status check remains; the prospect interaction
            // panel only exposes the operators still supported by this project.
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

        /**
         * Ouvre le panneau "Appeler via Kavkom avec IA". Contrairement au
         * clic-à-appeler classique, l'appel n'est pas lancé immédiatement :
         * le conseiller le démarre depuis le panneau, pour pouvoir se
         * préparer et pour ne pas solliciter le service IA tant que le
         * bridge ai-phone-agent n'est pas déployé.
         */
        interactionViaKavkomAI(number) {
            this.tab = 1;
            this.frameTab = 7;
            this.interaction = this.newInteraction();
            this.interaction.source = "ai_phone_agent";
            this.interaction.number = number;
            this.aiCallMessage = "";
            this.aiCallSuccess = false;
            this.pendingAINumber = "";
            this.aiCallTranscript = [];
            this.closeTranscriptWs();
        },

        async interactionViaKavkom(number) {
            this.tab = 1;
            this.frameTab = 2;
            this.interaction = this.newInteraction();
            this.interaction.source = "kavkom";
            this.interaction.number = number;
            // SIP events can arrive within milliseconds. Persist the
            // interaction first so they never update a stale record.
            try {
                await this.addInteraction();
            } catch (error) {
                console.error("Échec création interaction Kavkom", error);
                this.kavkomCallMessage =
                    "Impossible de créer l'interaction CRM avant l'appel.";
                this.kavkomCallSuccess = false;
                return;
            }
            this.triggerKavkomCall(number);
        },

        /**
         * Déclenche l'appel via l'API REST Kavkom (click-to-call officiel) :
         * POST /api/pbx/v1/active_call/call, relayé par le backend Laravel
         * (KavkomController::call). Le PBX Kavkom appelle d'abord notre
         * softphone (le "leg agent", extension sélectionnée), auquel le CRM répond
         * automatiquement pour éviter l'expiration du leg agent, puis
         * Kavkom.vue, puis met en relation avec le numéro de destination
         * de son côté — toute la négociation média PSTN reste côté Kavkom.
         */
        async triggerKavkomCall(number) {
            if (!number) {
                console.warn("[Kavkom] Appel ignoré : aucun numéro fourni.");
                return;
            }

            console.log("[Kavkom] Préparation de l'appel.", {
                prospectId: this.interactionProspect?.id,
                numberSuffix: String(number).replace(/\D/g, "").slice(-4),
            });

            // Le softphone doit être enregistré en SIP avant de pouvoir
            // recevoir/auto-répondre au leg agent. S'il ne l'est pas encore,
            // on mémorise le numéro et onKavkomReady relancera l'appel dès
            // que le softphone sera prêt.
            if (!this.kavkomReady) {
                console.log("[Kavkom] Appel en attente : softphone non prêt.");
                this.pendingKavkomNumber = number;
                this.kavkomCallMessage = "Connexion du softphone Kavkom…";
                this.kavkomCallSuccess = false;
                return;
            }

            this.callingViaKavkom = true;
            this.kavkomCallMessage = "";
            this.kavkomCallState = "requesting";

            // Kavkom rappelle notre extension (le "leg agent") : le
            // softphone doit l'accepter immédiatement, avant même que cette
            // requête REST ne réponde.
            kavkomPhone.expectAgentLeg();

            try {
                const { data } = await ApiService.post("settings/kavkom/call", {
                    destination: number,
                    prospect_id: this.interactionProspect?.id,
                    project_id: this.project?.id,
                });

                if (!data.success) {
                    console.warn("[Kavkom] L'API a refusé le lancement de l'appel.", { message: data.message });
                    // Refusé avant que Kavkom ne fasse sonner l'extension :
                    // le prochain INVITE n'est plus un leg agent.
                    kavkomPhone.forgetAgentLeg();
                    this.kavkomCallMessage =
                        data.message || "Impossible de lancer l'appel Kavkom.";
                    this.kavkomCallSuccess = false;
                    return;
                }

                // Kavkom may complete the agent leg before its REST response
                // returns. Never overwrite a newer SIP result with this
                // asynchronous acknowledgement.
                if (this.kavkomCallState === "requesting") {
                    this.kavkomCallMessage =
                        "Demande envoyée à Kavkom. Acceptez l'appel entrant pour être mis en relation avec le prospect.";
                    this.kavkomCallSuccess = true;
                    this.kavkomCallState = "requested";
                }
                console.log("[Kavkom] Appel lancé.", {
                    callUuid: data.call_uuid || null,
                    apiConfirmation: true,
                });
                if (data.call_uuid) {
                    this.kavkomCallUuid = data.call_uuid;
                    this.startKavkomDebugPolling(data.call_uuid);
                }
            } catch (error) {
                console.error("[Kavkom] Erreur lors du lancement de l'appel.", {
                    status: error.response?.status,
                    message: error.response?.data?.message || error.message,
                });
                // Kavkom can time out its HTTP response after it has already
                // sent the SIP INVITE. Preserve the newer SIP state instead
                // of hiding the Accept button behind a stale API error.
                if (this.kavkomCallState === "requesting") {
                    this.kavkomCallMessage =
                        error.response?.data?.message ||
                        "Kavkom n'a pas confirmé la demande à temps. Si l'appel démarre dans le CRM, ne relancez pas le bouton.";
                    this.kavkomCallSuccess = false;
                    this.kavkomCallState = "failed";
                }
            } finally {
                this.callingViaKavkom = false;
            }
        },

        /**
         * Lance un appel piloté par l'agent vocal IA ("Appeler avec l'IA").
         *
         * La requête part vers AiPhoneAgentController::trigger
         * (POST settings/ai-phone-agent/call), qui résout l'agent IA actif
         * du projet et l'extension Kavkom du conseiller, puis demande au
         * bridge Node ai-phone-agent de monter la conférence FreeSWITCH à
         * trois. Le bridge fait sonner l'extension du conseiller : le
         * softphone partagé doit donc auto-accepter ce leg, exactement
         * comme pour le clic-à-appeler Kavkom classique.
         *
         * Le service Node n'est pas encore déployé (projet démo) : en
         * attendant, le backend répond avec un message d'erreur explicite
         * que ce panneau affiche tel quel — rien à changer côté CRM le
         * jour où le bridge sera disponible.
         */
        async triggerAIAgentCall(number) {
            if (!number) {
                console.warn("[IA] Appel ignoré : aucun numéro fourni.");
                return;
            }

            if (!this.kavkomReady) {
                this.pendingAINumber = number;
                this.aiCallMessage = "Connexion du softphone Kavkom…";
                this.aiCallSuccess = false;
                return;
            }

            this.callingViaAIAgent = true;
            this.aiCallMessage = "";
            this.aiCallSuccess = false;

            kavkomPhone.expectAgentLeg();

            try {
                // L'interaction est enregistrée avant la requête : les
                // événements SIP du leg conseiller peuvent arriver avant
                // la réponse HTTP et doivent mettre à jour une interaction
                // déjà existante.
                this.interaction = this.newInteraction();
                this.interaction.source = "ai_phone_agent";
                this.interaction.number = number;
                await this.addInteraction();

                const { data } = await ApiService.post(
                    "settings/ai-phone-agent/call",
                    {
                        prospect_id: this.interactionProspect?.id,
                        destination: number,
                    }
                );

                if (!data.success) {
                    // Refusé avant que le bridge ne sonne le poste : le
                    // prochain INVITE n'est plus un leg agent.
                    kavkomPhone.forgetAgentLeg();
                    this.aiCallMessage =
                        data.message ||
                        "Impossible de lancer l'appel avec l'agent IA.";
                    this.aiCallSuccess = false;
                    return;
                }

                this.aiCallUuid = data.call_uuid || null;
                this.aiCallMessage =
                    "Agent IA en cours de connexion. Décrochez votre poste Kavkom pour rejoindre la conférence avec le prospect.";
                this.aiCallSuccess = true;
                console.log("[IA] Appel lancé.", {
                    callUuid: this.aiCallUuid,
                    prospectId: this.interactionProspect?.id,
                });

                // Ouvre le WebSocket de transcription live pour afficher
                // les échanges Gemini en temps réel dans le panneau.
                this.openTranscriptWs(this.aiCallUuid);
            } catch (error) {
                console.error("[IA] Erreur lors du lancement de l'appel.", {
                    status: error.response?.status,
                    message: error.response?.data?.message || error.message,
                });
                this.aiCallMessage =
                    error.response?.data?.message ||
                    "Impossible de joindre le service de l'agent vocal IA (ai-phone-agent).";
                this.aiCallSuccess = false;
            } finally {
                this.callingViaAIAgent = false;
            }
        },

        startKavkomDebugPolling(callUuid) {
            this.stopKavkomDebugPolling();
            this.kavkomDebugLastStatus = null;
            let polls = 0;

            console.log("[Kavkom][Debug] Server-side processing tracking enabled.", { callUuid });
            this.kavkomDebugTimer = window.setInterval(async () => {
                polls += 1;
                if (this.kavkomCallUuid !== callUuid) {
                    return;
                }
                try {
                    const { data } = await ApiService.get(
                        `settings/kavkom/call/${encodeURIComponent(callUuid)}/status`
                    );
                    // A request from a previous call can resolve after a
                    // new call starts. Never display that stale response.
                    if (this.kavkomCallUuid !== callUuid) {
                        return;
                    }
                    const signature = `${data.status}|${data.has_recording}|${data.processed_at}|${data.error || ""}`;
                    if (signature !== this.kavkomDebugLastStatus) {
                        this.kavkomDebugLastStatus = signature;
                        console.log("[Kavkom][Debug] Server processing status.", {
                            callUuid: data.call_uuid,
                            status: data.status,
                            hasRecording: data.has_recording,
                            processedAt: data.processed_at,
                            interactionId: data.interaction_id,
                            error: data.error || null,
                        });
                    }
                    if (["processed", "ignored"].includes(data.status) || polls >= 60) {
                        this.stopKavkomDebugPolling();
                    }
                } catch (error) {
                    // A 404 is normal until Kavkom has posted the CDR.
                    if (polls === 1 || polls % 6 === 0) {
                        console.debug("[Kavkom][Debug] CDR not received yet.", {
                            callUuid,
                            status: error.response?.status,
                        });
                    }
                }
            }, 5000);
        },

        stopKavkomDebugPolling() {
            if (this.kavkomDebugTimer) {
                window.clearInterval(this.kavkomDebugTimer);
                this.kavkomDebugTimer = null;
            }
        },

        /**
         * Ouvre le WebSocket de transcription live (port 14002) pour
         * recevoir les messages texte de Gemini en temps réel pendant
         * l'appel IA. Filtre par call_uuid pour n'afficher que les
         * messages de l'appel courant.
         */
        openTranscriptWs(callUuid) {
            this.closeTranscriptWs();
            this.aiCallTranscript = [];

            const wsUrl = `ws://${window.location.hostname}:14002`;
            const ws = new WebSocket(wsUrl);
            this.aiCallWs = ws;

            ws.onopen = () => {
                this.aiCallWsActive = true;
                console.log("[IA] Transcript WebSocket connected.", { callUuid });
            };

            ws.onmessage = (event) => {
                try {
                    const msg = JSON.parse(event.data);
                    if (msg.type === "transcript" && (!callUuid || msg.call_uuid === callUuid)) {
                        this.aiCallTranscript.push({
                            speaker: msg.speaker,
                            text: msg.text,
                            at: msg.at,
                        });
                        // Auto-scroll : on laisse le DOM se mettre à jour
                        // avant de scroller en bas.
                        this.$nextTick(() => {
                            const el = this.$refs.aiTranscriptBox;
                            if (el) el.scrollTop = el.scrollHeight;
                        });
                    }
                } catch (_) {
                    // Message non-JSON (ping, etc.) ignoré.
                }
            };

            ws.onerror = (err) => {
                console.warn("[IA] Transcript WebSocket error.", err);
            };

            ws.onclose = () => {
                this.aiCallWsActive = false;
                console.log("[IA] Transcript WebSocket closed.");
            };
        },

        closeTranscriptWs() {
            if (this.aiCallWs) {
                try { this.aiCallWs.close(); } catch (_) {}
                this.aiCallWs = null;
            }
            this.aiCallWsActive = false;
        },

        subscribeKavkomEvents() {
            EventBus.on(KAVKOM_EVENTS.READY, this.onKavkomReady);
            EventBus.on(KAVKOM_EVENTS.INCOMING_CALL, this.onKavkomIncomingCall);
            EventBus.on(KAVKOM_EVENTS.CALL_ANSWERED, this.onKavkomCallAnswered);
            EventBus.on(KAVKOM_EVENTS.CALL_HANGUP, this.onKavkomCallHangup);
            EventBus.on(KAVKOM_EVENTS.CALL_FAILED, this.onKavkomCallFailed);
            EventBus.on(
                KAVKOM_EVENTS.CONNECTION_ERROR,
                this.onKavkomConnectionError
            );
        },

        unsubscribeKavkomEvents() {
            EventBus.off(KAVKOM_EVENTS.READY, this.onKavkomReady);
            EventBus.off(
                KAVKOM_EVENTS.INCOMING_CALL,
                this.onKavkomIncomingCall
            );
            EventBus.off(
                KAVKOM_EVENTS.CALL_ANSWERED,
                this.onKavkomCallAnswered
            );
            EventBus.off(KAVKOM_EVENTS.CALL_HANGUP, this.onKavkomCallHangup);
            EventBus.off(KAVKOM_EVENTS.CALL_FAILED, this.onKavkomCallFailed);
            EventBus.off(
                KAVKOM_EVENTS.CONNECTION_ERROR,
                this.onKavkomConnectionError
            );
        },

        onKavkomReady() {
            this.kavkomReady = true;
            console.log("[Kavkom][Debug] SIP softphone ready.");

            if (this.pendingKavkomNumber) {
                const number = this.pendingKavkomNumber;
                this.pendingKavkomNumber = "";
                this.triggerKavkomCall(number);
            }

            if (this.pendingAINumber) {
                const number = this.pendingAINumber;
                this.pendingAINumber = "";
                this.triggerAIAgentCall(number);
            }
        },

        /**
         * Le softphone partagé distingue le leg agent d'un clic-à-appeler
         * (auto-répondu, il alimente l'interaction de cette slide) d'un vrai
         * appel entrant (l'agent décide, le widget global historise).
         */
        onKavkomIncomingCall({ direction, number, automatic } = {}) {
            if (direction === "inbound") {
                console.log("[Kavkom][Debug] Incoming call.", { number });
                this.kavkomCallState = "ringing";
                this.kavkomCallSuccess = true;
                this.kavkomCallMessage = `Appel entrant${
                    number ? " de " + number : ""
                } — répondez depuis la fenêtre d'appel.`;
                return;
            }

            console.log("[Kavkom][Debug] Agent leg ringing.", { automatic });
            this.interaction.status = "ringing";
            this.updateInteraction();
            this.kavkomCallState = "ringing";
            this.kavkomCallSuccess = true;
            this.kavkomCallMessage = "Connexion automatique de votre poste Kavkom…";
        },

        onKavkomCallAnswered({ direction } = {}) {
            if (direction === "inbound") {
                this.kavkomCallState = "active";
                this.kavkomCallSuccess = true;
                this.kavkomCallMessage = "Appel entrant en cours.";
                return;
            }

            console.log("[Kavkom][Debug] Call answered; media bridge active.");
            this.interaction.status = "answered";
            this.updateInteraction();
            this.kavkomCallState = "active";
            this.kavkomCallSuccess = true;
            this.kavkomCallMessage = "Appel Kavkom en cours.";
        },

        onKavkomConnectionError(message) {
            console.error("[Kavkom][Debug] SIP connection error.", { message });
            this.kavkomReady = false;
            this.callingViaKavkom = false;
            this.callingViaAIAgent = false;
            this.kavkomCallState = "failed";
            this.kavkomCallSuccess = false;
            this.kavkomCallMessage = message;
        },

        onKavkomCallFailed({ message, direction } = {}) {
            console.warn("[Kavkom][Debug] Call failed.", { message, direction });
            this.callingViaKavkom = false;
            this.callingViaAIAgent = false;
            this.kavkomCallState = "failed";
            this.kavkomCallSuccess = false;
            this.kavkomCallMessage = message;
        },

        onKavkomCallHangup({ durationMs = null, direction = null, missed = false } = {}) {
            if (direction === "inbound") {
                console.log("[Kavkom][Debug] Incoming call hangup.", {
                    durationMs,
                    missed,
                });
                this.kavkomCallState = missed ? "failed" : "completed";
                this.kavkomCallSuccess = !missed;
                this.kavkomCallMessage = missed
                    ? "Appel entrant manqué."
                    : "Appel entrant terminé.";
                this.callingViaKavkom = false;
                return;
            }

            console.log("[Kavkom][Debug] Call hangup.", { durationMs });
            this.interaction.status = "hangup";
            this.updateInteraction();
            this.callingViaKavkom = false;
            this.kavkomCallSuccess = !(durationMs !== null && durationMs < 5000);
            this.kavkomCallState = this.kavkomCallSuccess ? "completed" : "failed";
            this.kavkomCallMessage = this.kavkomCallSuccess
                ? "Appel terminé. La transcription sera traitée après réception de l'enregistrement Kavkom."
                : "Kavkom a fermé l'appel avant la mise en relation. Le leg agent a fonctionné ; consultez le CDR Kavkom pour le motif exact du numéro appelé.";
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
            if (!this.interaction || !this.interactionProspect?.id) {
                return;
            }

            // Ne rien tenter si aucun prospect n'est rattaché à l'interaction
            // en cours (ex: le leg agent Kavkom peut arriver avant que le
            // contexte prospect ne soit chargé).
            if (!this.interactionProspect) {
                this.logKavkomWarn?.(
                    "updateInteraction ignoré : aucun prospect actif"
                );
                return;
            }

            if (!this.interaction.id) {
                try {
                    this.interaction = await store.dispatch(
                        ADD_PROSPECT_INTERACTION,
                        this.interaction
                    );
                } catch (error) {
                    console.error("Échec création interaction", error);
                }
                return;
            }

            try {
                await store.dispatch(UPDATE_PROSPECT_INTERACTION, this.interaction);
            } catch (error) {
                console.error("Échec mise à jour interaction", error);
            }
        },

        /**
         *
         */
        nextInteraction() {
            if (
                this.selectedProspects.length - 1 >
                this.currentProspectIndex
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

        /**
         * A configured Kavkom "Line" requires all fields, enforced by
         * LineController's validation — its mere existence, assigned to
         * this agent, is enough to know the SIP identity is ready.
         */
        kavkomConfigured() {
            return this.lines.some(
                (line) =>
                    line.operator === "kavkom" &&
                    String(line.user_id) === String(this.user.id)
            );
        },

        /**
         * Ringover's live widget authenticates itself (Ringover's own
         * browser SSO) rather than using the CRM-stored config, so any
         * configured Ringover line in the project is enough — it isn't
         * tied to a specific agent the way Kavkom's SIP identity is.
         */
        ringoverConfigured() {
            return this.lines.some((line) => line.operator === "ringover");
        },

        currentProspect() {
            if (this.selectedProspects.length > this.currentProspectIndex) {
                return this.selectedProspects[this.currentProspectIndex];
            }

            return null;
        },

        /**
         * Webhook URL
         * for web service import
         */
        aircallWebhookURL: function () {
            return window.location.origin + "/webhook/aircall";
        },
    },
};
</script>
