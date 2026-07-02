import store from "@/store";

export default (order, productFields) => {
    const fields = [
        {
            slug: "name",
        },
        {
            slug: "description",
        },
        {
            slug: "currency",
        },
        {
            slug: "total_excluding_tax",
        },
        {
            slug: "total_including_tax",
        },
        {
            slug: "tax_value",
        },
    ];

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

            fields.forEach((f) => {
                field.content = field.content.replaceAll(
                    new RegExp(`\\{order.${f.slug}\\}`, "g"),
                    isNaN(order[f.slug])
                        ? order[f.slug]
                        : Math.round(order[f.slug] * 100) / 100
                );
            });

            for (let i = 0; i < 4; ++i) {
                productFields.forEach((f) => {
                    field.content = field.content.replaceAll(
                        new RegExp(
                            "\\{product\\[" +
                                i +
                                "\\]." +
                                (f.pivot ? "pivot." : "") +
                                (f.meta ? "meta." : "") +
                                f.slug +
                                "\\}",
                            "g"
                        ),
                        f.name + " produit " + (i + 1)
                    );
                });
            }

            store.getters.fields
                .filter((f) => f.for == "order")
                .forEach((f) => {
                    if (f.meta) {
                        field.content = field.content.replaceAll(
                            new RegExp(`\\{order.meta.${f.slug}\\}`, "g"),
                            "[Commande: " + f.name + "]"
                        );
                    } else {
                        field.content = field.content.replaceAll(
                            new RegExp(`\\{order.${f.slug}\\}`, "g"),
                            "[Commande: " + f.name + "]"
                        );
                    }
                });

            return field.content;
        },
    };
};
