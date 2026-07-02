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

            const regex = RegExp(`\\{prospect.category.(\\d+)\\}`, "g");
            let content = field.content;
            let matches;

            while ((matches = regex.exec(content)) !== null) {
                const id = matches[1];
                const category = store.getters.categories.find(
                    (c) => c.id == id
                );

                if (category) {
                    content = content.replaceAll(
                        new RegExp(`\\{prospect.category.${id}\\}`, "g"),
                        "[" + category.name + "]"
                    );
                }
            }

            return content;
        },
    };
};
