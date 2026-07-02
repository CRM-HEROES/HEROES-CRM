export default () => {
    const escapeHtml = (text) => {
        var map = {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#039;",
        };

        return text.replaceAll(/[&<>"']/g, function (m) {
            return map[m];
        });
    };

    return {
        render(field) {
            if (field.type != "text") {
                return field.content;
            }

            return escapeHtml(field.content);
        },
    };
};
