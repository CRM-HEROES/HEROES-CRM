export default () => {
    return {
        render(field) {
            if (field.type != "text" && field.type != "html") {
                return field.content;
            }

            return field.content.replaceAll(/{page}/g, "[Page]");
        },
    };
};
