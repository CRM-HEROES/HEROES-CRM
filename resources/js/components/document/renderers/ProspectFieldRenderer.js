import store from "@/store";

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

            store.getters.fields
                .filter((f) => f.for == "prospect")
                .forEach((f) => {
                    if (f.meta) {
                        field.content = field.content.replaceAll(
                            new RegExp(`\\{prospect.meta.${f.slug}\\}`, "g"),
                            "[Prospect: " + f.name + "]"
                        );
                    } else {
                        field.content = field.content.replaceAll(
                            new RegExp(`\\{prospect.${f.slug}\\}`, "g"),
                            "[Prospect: " + f.name + "]"
                        );
                    }
                });
            return field.content;
        },
    };
};
