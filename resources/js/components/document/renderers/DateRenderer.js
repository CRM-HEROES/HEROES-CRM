export default (date) => {
    return {
        render(field) {
            if (
                field.type != "text" &&
                field.type != "html" &&
                field.type != "qrcode"
            ) {
                return field.content;
            }

            const regex = RegExp(`\\{date.([\\-\\/\\w]+)\\}`, "g");
            let content = field.content;
            let matches;

            while ((matches = regex.exec(content)) !== null) {
                const format = matches[1];
                const regexFormat = format.replaceAll(
                    /[.*+?^${}()|[\]\\]/g,
                    "\\$&"
                );
                content = content.replaceAll(
                    new RegExp(`\\{date.${regexFormat}\\}`, "g"),
                    dayjs(date).format(format)
                );
            }

            return content;
        },
    };
};
