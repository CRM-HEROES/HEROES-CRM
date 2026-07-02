export default (lat, lng) => {
    return {
        render(field) {
            if (field.type != "map") {
                return field.content;
            }

            let content = field.content;
            if (typeof content !== "object") {
                content = JSON.parse(content);
            }

            const zoom = content.zoom
                ? Math.min(Math.max(parseInt(content.zoom), 10), 20)
                : 18;
            const type = content.type ? content.type : "satellite";
            const width = /*field.style.width ? parseInt(field.style.width) : */ 400;
            const height = /*field.style.height
                ? parseInt(field.style.height)
                : */ 400;

            return `<img style="user-drag: none; -webkit-user-drag: none; width: 100%" src="https://maps.googleapis.com/maps/api/staticmap?center=${lat},${lng}&zoom=${zoom}&size=${width}x${height}&maptype=${type}&key=AIzaSyCeXgdUFop51vFS_x8Bj5JlrCOZak8h4NA" />`;
        },
    };
};
