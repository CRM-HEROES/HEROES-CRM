export default () => {
    return {
        render(field) {
            if (field.type != "image") {
                return field.content;
            }

            return `<img style="user-drag: none; -webkit-user-drag: none; width: 100%" src="${field.content}" />`;
        },
    };
};
