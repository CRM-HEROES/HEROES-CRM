import store from "@/store";

export default () => {
    return {
        render(field) {
            if (
                field.type != "text" &&
                field.type != "html" &&
                field.type != "qrcode"
            ) {
                return field.content;
            }

            store.getters.fields
                .filter((f) => f.for == "user")
                .forEach((f) => {
                    if (f.meta) {
                        field.content = field.content.replaceAll(
                            new RegExp(`\\{user.meta.${f.slug}\\}`, "g"),
                            "[Utilisateur: " + f.name + "]"
                        );
                    } else {
                        field.content = field.content.replaceAll(
                            new RegExp(`\\{user.${f.slug}\\}`, "g"),
                            "[Utilisateur: " + f.name + "]"
                        );
                    }
                });
            return field.content;
        },
    };
};
