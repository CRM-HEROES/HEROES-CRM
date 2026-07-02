export default () => {
    return {
        render(field) {
            if (field.type != "prospect-option") {
                return field.content;
            }

            return `<div style="min-width: 18px; min-height: 18px; width: 100%; height: 100%; border: 1px solid #7939b8; background-color: #FFF; margin: 0; text-align: center;">
                <span style="display: inline-block; width: 10px; height: 5px; border-top: 3px solid #aaa; border-right: 2px solid #555; transform: rotate(135deg); position: relative; top: -3px; left: 3px;"></span>
                <span style="display: inline-block; height: 100%; vertical-align: middle;"></span>
            </div>`;
        },
    };
};
