export default {
    mounted(el, binding) {
        // Define the mouseover event handler
        const onMouseOver = () => {
            const position = binding.modifiers.top ? 
                "bottom" : (
                    binding.modifiers.right ? 
                        "left" : (
                            binding.modifiers.bottom ? 
                                "top" : (
                                    binding.modifiers.left ? 
                                        "right" : null
                                )
                        )
                );

            tooltip(el, binding.value, position);
        };

        // Define the mouseout event handler
        const onMouseOut = () => {
            tooltip();
        };

        // Add event listeners
        el.addEventListener("mouseover", onMouseOver);
        el.addEventListener("mouseout", onMouseOut);

        // Store the event listeners so that they can be removed later
        el.__hover_directive_handlers__ = {
            onMouseOver,
            onMouseOut,
        };
    },
    unmounted(el) {
        // Remove event listeners
        const { onMouseOver, onMouseOut } = el.__hover_directive_handlers__;
        el.removeEventListener("mouseover", onMouseOver);
        el.removeEventListener("mouseout", onMouseOut);

        // Clean up
        delete el.__hover_directive_handlers__;
    },
};
