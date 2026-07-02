export default () => {
    return {
        render(field) {
            if (
                field.type != "text" &&
                field.type != "html" &&
                field.type != "qrcode" &&
                field.type != "order-table"
            ) {
                return field.content;
            }

            field.content = field.content.replaceAll(
                new RegExp(`\\{invoice.id\\}`, "g"),
                "XXXXXX"
            );

            field.content = field.content.replaceAll(
                new RegExp(`\\{invoice.date\\}`, "g"),
                "dd/mm/YYYY"
            );

            return field.content;
        },
    };
};
