export default {
    mounted: function (el, binding) {
        const value = binding.value;

        if (value) {
            tuto(
                el,
                value.body,
                value.key,
                value.name,
                value.timeout ? value.timeout : 0
            );
        }
    },
};
