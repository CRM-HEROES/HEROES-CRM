export default (order, fields) => {
    /**
     * Product fields
     */
    const productFields = [
        "currency",
        "description",
        "name",
        "price",
        "tax",
        "price_value",
        "price_excluding_tax",
        "price_including_tax",
        "tax_percent",
        "tax_value",
    ];

    /**
     * Product fields
     */
    const productPivotFields = [
        "quantity",
        "currency",
        "price",
        "tax",
        "price_value",
        "tax_percent",
        "price_excluding_tax",
        "price_including_tax",
        "tax_value",
        "total_price_excluding_tax",
        "total_price_including_tax",
        "total_tax_value",
    ];

    /**
     * Order fields
     */
    const orderFields = [
        "name",
        "description",
        "currency",
        "total_excluding_tax",
        "total_including_tax",
        "tax_value",
    ];

    const orderProductFields =
        fields && fields.orderProduct ? fields.orderProduct : [];

    /**
     * Get table styles
     */
    const getStyles = (column, key) => {
        const style = column[key].style || {};

        return Object.entries(style)
            .map(([key, value]) => `${key}: ${value}`)
            .join(";");
    };

    /**
     * Get product image
     */
    const getProductImage = (column, product) => {
        return `<img src="https://heroescrm.pro/favicon.ico" style="width: 100%;" />`;
    };

    /**
     * Get table row content
     */
    const getRowContent = (column, product, index) => {
        let content = column.body.content || "";

        // Replace product field placeholders with actual values
        for (let field of productFields) {
            const placeholder = `{product.${field}}`;
            const value = product[field];
            content = content.replaceAll(
                placeholder,
                isNaN(value) ? value : Math.round(value * 100) / 100
            );
        }

        // Replace {product.image} with the product image
        if (content.includes("{product.image}")) {
            content = content.replaceAll(
                "{product.image}",
                getProductImage(column, product)
            );
        }

        // Replace {product.index} with the product index
        if (content.includes("{product.index}")) {
            content = content.replaceAll("{product.index}", index + 1);
        }

        // Replace product pivot field placeholders with actual values
        for (let field of productPivotFields) {
            const placeholder = `{product.pivot.${field}}`;
            const value = product.pivot[field];
            content = content.replaceAll(
                placeholder,
                isNaN(value) ? value : Math.round(value * 100) / 100
            );
        }

        // Replace product pivot field placeholders with actual values
        for (let field of orderProductFields) {
            const placeholder = `{product.pivot.meta.${field.slug}}`;
            const value = `${field.name}`;
            content = content.replaceAll(
                placeholder,
                isNaN(value) ? value : Math.round(value * 100) / 100
            );
        }

        return content;
    };

    /**
     * Get table header cell
     */
    const getHeaderCell = (column) => {
        const content = column.header.content || "";
        const styles = getStyles(column, "header");

        return `<th style="${styles}">${content}</th>`;
    };

    /**
     * Get table body cell
     */
    const getBodyCell = (column, product, index) => {
        const content = getRowContent(column, product, index);
        const styles = getStyles(column, "body");

        return `<td style="${styles}">${content}</td>`;
    };

    /**
     * Get table body row
     */
    const getBodyRow = (columns, product, index) => {
        const cells = columns
            .map((column) => getBodyCell(column, product, index))
            .join("");
        return `<tr>${cells}</tr>`;
    };

    /**
     * Get table header
     */
    const getHeader = (columns) => {
        return columns.map((column) => getHeaderCell(column)).join("");
    };

    /**
     * Get table body
     */
    const getBody = (columns) => {
        return order.products.reduce(
            (carry, product, index) =>
                carry + getBodyRow(columns, product, index),
            ""
        );
    };

    /**
     * Get table
     */
    const getTable = (columns) => {
        return `
            <table style="border-collapse: collapse; width: 100%; position: relative; top: 0; left: 0">
                <thead style="width: 100%;">
                    ${getHeader(columns)}
                </thead>
                <tbody>
                    ${getBody(columns)}
                </tbody>
            </table>
        `;
    };

    /**
     * Get summary header cell
     */
    const getSummaryHeaderCell = (summary) => {
        const content = summary.header.content || "";
        const styles = getStyles(summary, "header");

        return `<td style="${styles}">${content}</td>`;
    };

    /**
     * Get summary body cell
     */
    const getSummaryBodyCell = (summary) => {
        const styles = getStyles(summary, "body");

        let content = summary.body.content || "";

        // Replace order field placeholders with actual values
        for (const field of orderFields) {
            const placeholder = `{order.${field}}`;
            const value = order[field];
            content = content.replaceAll(
                placeholder,
                isNaN(value) ? value : Math.round(value * 100) / 100
            );
        }

        return `<td style="${styles}">${content}</td>`;
    };

    /**
     * Get summary body cell
     */
    const getSummaryRow = (summary) => {
        return `<tr>${getSummaryHeaderCell(summary)}${getSummaryBodyCell(
            summary
        )}</tr>`;
    };

    /**
     * Get summary
     */
    const getSummary = (summaries) => {
        if (summaries.length === 0) {
            return "";
        }

        return `
            <div style="width: 100%; position: relative; margin-top: 10px">
                <table style="border-collapse: collapse; width: auto; background: white; float: right">
                    <tbody>
                        ${summaries.reduce(
                            (carry, summary) => carry + getSummaryRow(summary),
                            ""
                        )}
                    </tbody>
                </table>
            </div>
        `;
    };

    /**
     * Render a document field
     *
     * @param  content
     */
    return {
        render(field) {
            if (field.type != "order-table") {
                return field.content;
            }

            let content = field.content;
            if (typeof content !== "object") {
                content = JSON.parse(content);
            }

            const columns = content.columns || [];
            const summaries = content.summaries || [];

            return getTable(columns) + getSummary(summaries);
        },
    };
};
