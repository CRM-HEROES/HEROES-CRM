const state = {
    draft: null,
};

const mutations = {
    SET_PROSPECT_EMAIL_DRAFT(state, draft) {
        state.draft = draft;
    },
    CLEAR_PROSPECT_EMAIL_DRAFT(state) {
        state.draft = null;
    },
};

const getters = {
    prospectEmailDraft: (state) => state.draft,
};

export default { state, mutations, getters };