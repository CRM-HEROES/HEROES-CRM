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

            field.content = field.content.replaceAll(
                new RegExp(`\\{project.logo\\}`, "g"),
                `<img src="${store.getters.project.thumbnail}" style="width: 100%" />`
            );

            store.getters.fields
                .filter((f) => f.for == "project")
                .forEach((f) => {
                    if (f.meta) {
                        field.content = field.content.replaceAll(
                            new RegExp(`\\{project.meta.${f.slug}\\}`, "g"),
                            "[Project: " + f.name + "]"
                        );
                    } else {
                        field.content = field.content.replaceAll(
                            new RegExp(`\\{project.${f.slug}\\}`, "g"),
                            "[Project: " + f.name + "]"
                        );
                    }
                });
            return field.content;
        },
    };
};
