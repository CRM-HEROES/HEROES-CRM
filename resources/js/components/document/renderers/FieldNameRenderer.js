import store from "@/store";

export default () => {
    return {
        render(field) {
            if (!field.content) {
                return "";
            }

            if (field.type != "text" && field.type != "html") {
                return field.content;
            }

            store.getters.fields
                .filter((f) => f.for == "order")
                .forEach((f) => {
                    if (f.meta) {
                        field.content = field.content.replaceAll(
                            new RegExp(`\\{field.${f.slug}\\}`, "g"),
                            f.name
                        );
                    } else {
                        field.content = field.content.replaceAll(
                            new RegExp(`\\{field.${f.slug}\\}`, "g"),
                            f.name
                        );
                    }
                });
            return field.content;
        },
    };
};
