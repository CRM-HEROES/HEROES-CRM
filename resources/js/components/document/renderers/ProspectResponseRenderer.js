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

            const regex = RegExp(`\\{prospect.question.(\\d+)\\}`, "g");
            let content = field.content;
            let matches;

            while ((matches = regex.exec(content)) !== null) {
                const id = matches[1];

                content = content.replaceAll(
                    new RegExp(`\\{prospect.question.${id}\\}`, "g"),
                    "[Question: " + id + "]"
                );
            }

            return content;
        },
    };
};
