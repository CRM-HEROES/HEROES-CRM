/**
 * Fake order
 * for document order field
 */
export const fakeOrder = (() => {
    // Fake order
    const order = {
        name: "Commande test",
        description: "Produit 1",
        total_excluding_tax: 0,
        tax_value: 0,
        total_including_tax: 0,
        currency: "€",
    };

    // Fake products
    const products = [];
    const productsCount = 3;

    // Generate productsCount fake products
    for (var i = 1; i <= productsCount; ++i) {
        const quantity = parseInt(Math.random() * 4) + 1;
        const product = {
            name: "Produit " + i,
            currency: "€",
            description: "Description produit " + i,
            tax: parseInt(Math.random() * 20) / 100,
            price: parseInt(Math.random() * 20) * 10 + 100,
        };

        // Calculate product tax
        product.price_value = product.price + product.currency + " HT";
        product.tax_percent = product.tax * 100;
        product.tax_value = product.tax * product.price;
        product.price_excluding_tax = product.price;
        product.price_including_tax = product.price * (1 + product.tax);

        product.pivot = {
            quantity: quantity,
            currency: product.currency,
            price: product.price,
            tax: product.tax,
            price_value: product.price_value,
            tax_percent: product.tax_percent,
            price_excluding_tax: product.price_excluding_tax,
            price_including_tax: product.price_including_tax,
            tax_value: product.tax_value,
            total_price_excluding_tax: product.price_excluding_tax * quantity,
            total_price_including_tax: product.price_including_tax * quantity,
            total_tax_value: product.tax_value * quantity,
        };

        products.push(product);
    }

    // Calculate order total
    products.forEach((product) => {
        order.total_excluding_tax += product.pivot.total_price_excluding_tax;
        order.tax_value += product.pivot.total_tax_value;
        order.total_including_tax += product.pivot.total_price_including_tax;
    });

    order.products = products;

    return order;
})();

/**
 * Fake order field
 */
export const fakeOrderField = {
    id: 0,
    type: "order-table",
    content: JSON.stringify({
        columns: [
            {
                header: {
                    content: "Nom du produit",
                    style: {
                        border: "1px solid #ccc",
                        "font-size": "12px",
                        "vertical-align": "middle",
                        "text-align": "center",
                        "background-color": "#f6f6f9",
                        "font-weight": "normal",
                        "white-space": "nowrap",
                        height: "36px",
                    },
                },
                body: {
                    content: "<b>{product.name}</b><br />{product.description}",
                    style: {
                        "background-color": "white",
                        border: "1px solid #DDDDDD",
                        padding: "3px 5px",
                    },
                },
            },
            {
                header: {
                    content: "Prix",
                    style: {
                        border: "1px solid #ccc",
                        "font-size": "12px",
                        "vertical-align": "middle",
                        "text-align": "center",
                        "background-color": "#f6f6f9",
                        "font-weight": "normal",
                        "white-space": "nowrap",
                        height: "36px",
                        width: "80px",
                    },
                },
                body: {
                    content: "{product.pivot.price_value}",
                    style: {
                        "background-color": "white",
                        border: "1px solid #DDDDDD",
                        padding: "3px 5px",
                        "text-align": "right",
                        "font-size": "13px",
                    },
                },
            },
            {
                header: {
                    content: "Taxe",
                    style: {
                        border: "1px solid #ccc",
                        "font-size": "12px",
                        "vertical-align": "middle",
                        "text-align": "center",
                        "background-color": "#f6f6f9",
                        "font-weight": "normal",
                        "white-space": "nowrap",
                        height: "36px",
                        width: "60px",
                    },
                },
                body: {
                    content: "{product.pivot.tax_percent} %",
                    style: {
                        "background-color": "white",
                        border: "1px solid #DDDDDD",
                        padding: "3px 5px",
                        "text-align": "right",
                        "font-size": "13px",
                    },
                },
            },
            {
                header: {
                    content: "Qté",
                    style: {
                        border: "1px solid #ccc",
                        "font-size": "12px",
                        "vertical-align": "middle",
                        "text-align": "center",
                        "background-color": "#f6f6f9",
                        "font-weight": "normal",
                        "white-space": "nowrap",
                        height: "36px",
                        width: "40px",
                    },
                },
                body: {
                    content: "{product.pivot.quantity}",
                    style: {
                        "background-color": "white",
                        border: "1px solid #DDDDDD",
                        padding: "3px 5px",
                        "text-align": "right",
                    },
                },
            },
            {
                header: {
                    content: "Taxe total",
                    style: {
                        border: "1px solid #ccc",
                        "font-size": "12px",
                        "vertical-align": "middle",
                        "text-align": "center",
                        "background-color": "#f6f6f9",
                        "font-weight": "normal",
                        "white-space": "nowrap",
                        height: "36px",
                        width: "70px",
                    },
                },
                body: {
                    content:
                        "{product.pivot.total_tax_value} {product.pivot.currency}",
                    style: {
                        "background-color": "white",
                        border: "1px solid #DDDDDD",
                        padding: "3px 5px",
                        "text-align": "right",
                        "font-size": "13px",
                    },
                },
            },
            {
                header: {
                    content: "Sous total HT",
                    style: {
                        border: "1px solid #ccc",
                        "font-size": "12px",
                        "vertical-align": "middle",
                        "text-align": "center",
                        "background-color": "#f6f6f9",
                        "font-weight": "normal",
                        "white-space": "nowrap",
                        height: "36px",
                        width: "70px",
                    },
                },
                body: {
                    content:
                        "{product.pivot.total_price_excluding_tax} {product.pivot.currency}",
                    style: {
                        "background-color": "white",
                        border: "1px solid #DDDDDD",
                        padding: "3px 5px",
                        "text-align": "right",
                        "font-size": "13px",
                    },
                },
            },
            {
                header: {
                    content: "Sous total TTC",
                    style: {
                        border: "1px solid #ccc",
                        "font-size": "12px",
                        "vertical-align": "middle",
                        "text-align": "center",
                        "background-color": "#ffe4bb",
                        "font-weight": "normal",
                        "white-space": "nowrap",
                        height: "36px",
                        width: "70px",
                    },
                },
                body: {
                    content:
                        "{product.pivot.total_price_including_tax} {product.pivot.currency}",
                    style: {
                        "background-color": "#fff6e9",
                        border: "1px solid #DDDDDD",
                        padding: "3px 5px",
                        "text-align": "right",
                        "font-size": "13px",
                    },
                },
            },
            /*{
                header: {
                    content: "Photo",
                    style: {
                        border: "1px solid #CCC",
                        "font-size": "12px",
                        "vertical-align": "middle",
                        "text-align": "center",
                        "background-color": "#f6f6f9",
                        "font-weight": "normal",
                        "white-space": "nowrap",
                        height: "36px",
                        width: "70px",
                    },
                },
                body: {
                    content: "{product.image}",
                    style: {
                        border: "1px solid #DDDDDD",
                        padding: "2px",
                    },
                },
            },*/
        ],
        summaries: [
            {
                header: {
                    content: "Total HT : ",
                    style: {
                        padding: "2px 5px",
                        "font-weight": "bold",
                    },
                },
                body: {
                    content: "{order.total_excluding_tax} {order.currency}",
                    style: {
                        padding: "2px 5px",
                        "text-align": "right",
                    },
                },
            },
            {
                header: {
                    content: "TVA : ",
                    style: {
                        padding: "2px 5px",
                        "font-weight": "bold",
                    },
                },
                body: {
                    content: "{order.tax_value} {order.currency}",
                    style: {
                        padding: "2px 5px",
                        "text-align": "right",
                    },
                },
            },
            {
                header: {
                    content: "Total TTC : ",
                    style: {
                        padding: "2px 5px",
                        "font-weight": "bold",
                    },
                },
                body: {
                    content: "{order.total_including_tax} {order.currency}",
                    style: {
                        padding: "2px 5px",
                        "text-align": "right",
                    },
                },
            },
        ],
    }),
    style: {
        width: "720px",
        height: "auto",
        "font-size": "12px",
    },
};

export const invoiceTemplate = {};
