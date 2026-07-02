export default () => {
    return {
        render(field) {
            if (field.type != "qrcode") {
                return field.content;
            }

            return `<img style="user-drag: none; -webkit-user-drag: none; width: 100%" title="${field.content}" src="${window.location.origin}/images/document/qrcode.jpg" />`;
        },
    };
};
