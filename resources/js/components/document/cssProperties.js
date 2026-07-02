export default {
    /**
     * Background Color
     */
    backgroundColor: {
        name: "background-color",
        default: "#000000",
    },

    /**
     * Font weign bold
     */
    bold: {
        name: "font-weight",
        default: false,
        get: (value) => value === "bold",
        set: (value) => (value ? "bold" : "normal"),
    },

    /**
     * Border Color
     */
    borderColor: {
        name: "border-color",
        default: "#000000",
    },

    /**
     * Border Radius
     */
    borderRadius: {
        name: "border-radius",
        default: 0,
        get: (value) => {
            const [p1, p2, p3, p4] = value.replace(/\s+/g, " ").split(" ");
            if (p4) {
                return p1 == p2 && p1 == p3 && p1 == p4 ? parseInt(p1) : "";
            } else if (p3) {
                return p1 == p2 && p1 == p3 ? parseInt(p1) : "";
            } else if (p2) {
                return p1 == p2 ? parseInt(p1) : "";
            }

            return parseInt(p1);
        },
        set: (value) => {
            return value + "px";
        },
    },

    /**
     * Bottom Left Border Radius
     */
    borderRadiusBL: {
        name: "border-radius",
        default: 0,
        get: (value) => {
            const [p1, p2, p3, p4] = value.replace(/\s+/g, " ").split(" ");
            if (p4) {
                return parseInt(p4);
            } else if (p3 || p2) {
                return parseInt(p2);
            }

            return parseInt(p1);
        },
        set: (value, current) => {
            value += "px";
            if (!current) {
                return `0px 0px 0px ${value}`;
            }

            const [p1, p2, p3] = current.replace(/\s+/g, " ").split(" ");
            return p3
                ? `${p1} ${p2} ${p3} ${value}`
                : p2
                ? `${p1} ${p2} ${p1} ${value}`
                : `${p1} ${p1} ${p1} ${value}`;
        },
    },

    /**
     * Bottom Right Border Radius
     */
    borderRadiusBR: {
        name: "border-radius",
        default: 0,
        get: (value) => {
            const [p1, , p3] = value.replace(/\s+/g, " ").split(" ");
            if (p3) {
                return parseInt(p3);
            }

            return parseInt(p1);
        },
        set: (value, current) => {
            value += "px";
            if (!current) {
                return `0px 0px ${value} 0px`;
            }

            const [p1, p2, p3, p4] = current.replace(/\s+/g, " ").split(" ");
            return p4
                ? `${p1} ${p2} ${value} ${p4}`
                : p3
                ? `${p1} ${p2} ${value}`
                : p2
                ? `${p1} ${p2} ${value} ${p2}`
                : `${p1} ${p1} ${value} ${p1}`;
        },
    },

    /**
     * Top Left Border Radius
     */
    borderRadiusTL: {
        name: "border-radius",
        default: 0,
        get: (value) => {
            const [p1] = value.replace(/\s+/g, " ").split(" ");
            return parseInt(p1);
        },
        set: (value, current) => {
            value += "px";
            if (!current) {
                return `${value} 0px 0px 0px`;
            }

            const [p1, p2, p3, p4] = current.replace(/\s+/g, " ").split(" ");
            return p4
                ? `${value} ${p2} ${p3} ${p4}`
                : p3
                ? `${value} ${p2} ${p3} ${p2}`
                : p2
                ? `${value} ${p2} ${p1} ${p2}`
                : `${value} ${p1} ${p1} ${p1}`;
        },
    },

    /**
     * Top Right Border Radius
     */
    borderRadiusTR: {
        name: "border-radius",
        default: 0,
        get: (value) => {
            const [p1, p2] = value.replace(/\s+/g, " ").split(" ");
            if (p2) {
                return parseInt(p2);
            }

            return parseInt(p1);
        },
        set: (value, current) => {
            value += "px";
            if (!current) {
                return `0px ${value} 0px 0px`;
            }

            const [p1, p2, p3, p4] = current.replace(/\s+/g, " ").split(" ");
            return p4
                ? `${p1} ${value} ${p3} ${p4}`
                : p3
                ? `${p1} ${value} ${p3} ${p2}`
                : p2
                ? `${p1} ${value} ${p1} ${p2}`
                : `${p1} ${value} ${p1} ${p1}`;
        },
    },

    /**
     * Border style
     */
    borderStyle: {
        name: "border-style",
        default: "",
    },

    /**
     * Bottom Border Width
     */
    borderBottomWidth: {
        name: "border-width",
        default: 0,
        get: (value) => {
            const [p1, , p3] = value.replace(/\s+/g, " ").split(" ");
            if (p3) {
                return parseInt(p3);
            }

            return parseInt(p1);
        },
        set: (value, current) => {
            if (!value) {
                value = 0;
            }

            value += "px";
            if (!current) {
                return `0px 0px ${value} 0px`;
            }

            const [p1, p2, p3, p4] = current.replace(/\s+/g, " ").split(" ");
            return p4
                ? `${p1} ${p2} ${value} ${p4}`
                : p3
                ? `${p1} ${p2} ${value}`
                : p2
                ? `${p1} ${p2} ${value} ${p2}`
                : `${p1} ${p1} ${value} ${p1}`;
        },
    },

    /**
     * Left Border Width
     */
    borderLeftWidth: {
        name: "border-width",
        default: 0,
        get: (value) => {
            const [p1, p2, p3, p4] = value.replace(/\s+/g, " ").split(" ");
            if (p4) {
                return parseInt(p4);
            } else if (p3 || p2) {
                return parseInt(p2);
            }

            return parseInt(p1);
        },
        set: (value, current) => {
            if (!value) {
                value = 0;
            }

            value += "px";
            if (!current) {
                return `0px 0px 0px ${value}`;
            }

            const [p1, p2, p3] = current.replace(/\s+/g, " ").split(" ");
            return p3
                ? `${p1} ${p2} ${p3} ${value}`
                : p2
                ? `${p1} ${p2} ${p1} ${value}`
                : `${p1} ${p1} ${p1} ${value}`;
        },
    },

    /**
     * Top Border Width
     */
    borderTopWidth: {
        name: "border-width",
        default: 0,
        get: (value) => {
            const [p1] = value.replace(/\s+/g, " ").split(" ");
            return parseInt(p1);
        },
        set: (value, current) => {
            if (!value) {
                value = 0;
            }

            value += "px";
            if (!current) {
                return `${value} 0px 0px 0px`;
            }

            const [p1, p2, p3, p4] = current.replace(/\s+/g, " ").split(" ");
            return p4
                ? `${value} ${p2} ${p3} ${p4}`
                : p3
                ? `${value} ${p2} ${p3} ${p2}`
                : p2
                ? `${value} ${p2} ${p1} ${p2}`
                : `${value} ${p1} ${p1} ${p1}`;
        },
    },

    /**
     * Right Border Width
     */
    borderRightWidth: {
        name: "border-width",
        default: 0,
        get: (value) => {
            if (!value) {
                value = 0;
            }

            const [p1, p2] = value.replace(/\s+/g, " ").split(" ");
            if (p2) {
                return parseInt(p2);
            }

            return parseInt(p1);
        },
        set: (value, current) => {
            value += "px";
            if (!current) {
                return `0px ${value} 0px 0px`;
            }

            const [p1, p2, p3, p4] = current.replace(/\s+/g, " ").split(" ");
            return p4
                ? `${p1} ${value} ${p3} ${p4}`
                : p3
                ? `${p1} ${value} ${p3} ${p2}`
                : p2
                ? `${p1} ${value} ${p1} ${p2}`
                : `${p1} ${value} ${p1} ${p1}`;
        },
    },

    /**
     * Border Width
     */
    borderWidth: {
        name: "border-width",
        default: 0,
        get: (value) => {
            const [p1, p2, p3, p4] = value.replace(/\s+/g, " ").split(" ");
            if (p4) {
                return p1 == p2 && p1 == p3 && p1 == p4 ? parseInt(p1) : "";
            } else if (p3) {
                return p1 == p2 && p1 == p3 ? parseInt(p1) : "";
            } else if (p2) {
                return p1 == p2 ? parseInt(p1) : "";
            }

            return parseInt(p1);
        },
        set: (value) => {
            if (!value) {
                value = 0;
            }

            return value + "px";
        },
    },

    /**
     * Text Color
     */
    color: {
        name: "color",
        default: "#000000",
    },

    /**
     * Text overlow ellipsis
     */
    ellipsis: {
        name: "text-overflow",
        default: false,
        get: (value) => value === "ellipsis",
        set: (value) => (value ? "ellipsis" : "initial"),
    },

    /**
     * Test style italic
     */
    italic: {
        name: "font-style",
        default: false,
        get: (value) => value === "italic",
        set: (value) => (value ? "italic" : "normal"),
    },

    /**
     * Font family
     */
    fontFamily: {
        name: "font-family",
        default: "arial",
    },

    /**
     * Font size
     */
    fontSize: {
        name: "font-size",
        default: "12px",
    },

    /**
     * Font style
     */
    fontStyle: {
        name: "font-style",
        default: "normal",
    },

    /**
     * Font weigth
     */
    fontWeight: {
        name: "font-weight",
        default: "normal",
    },

    /**
     * Height
     */
    height: {
        name: "height",
        default: "",
        get: (value) => (value == "auto" ? "" : parseInt(value)),
        set: (value) => (value ? parseInt(value) + "px" : "auto"),
    },

    /**
     * Left position
     */
    left: {
        name: "left",
        default: "",
        get: (value) => (value == "auto" ? "" : parseInt(value)),
        set: (value) => (value ? parseInt(value) + "px" : "auto"),
    },

    /**
     * Letter spacing
     */
    letterSpacing: {
        name: "letter-spacing",
        default: "",
        get: (value) => (value == "auto" ? "" : parseInt(value)),
        set: (value) => (value ? parseInt(value) + "px" : "auto"),
    },

    /**
     * Line height
     */
    lineHeight: {
        name: "line-height",
        default: "normal",
        get: (value) => (value == "auto" ? "" : parseInt(value)),
        set: (value) => (value ? parseInt(value) + "px" : "auto"),
    },

    /**
     * Line through
     */
    lineThrough: {
        name: "text-decoration",
        default: false,
        get: (value) => value === "line-through",
        set: (value) => (value ? "line-through" : "none"),
    },

    /**
     * Overflow hidden
     */
    overflowHidden: {
        name: "overflow",
        default: false,
        get: (value) => value === "hidden",
        set: (value) => (value ? "hidden" : "auto"),
    },

    /**
     * Padding
     */
    padding: {
        name: "padding",
        default: 0,
        get: (value) => {
            const [p1, p2, p3, p4] = value.replace(/\s+/g, " ").split(" ");
            if (p4) {
                return p1 == p2 && p1 == p3 && p1 == p4 ? parseInt(p1) : "";
            } else if (p3) {
                return p1 == p2 && p1 == p3 ? parseInt(p1) : "";
            } else if (p2) {
                return p1 == p2 ? parseInt(p1) : "";
            }

            return parseInt(p1);
        },
        set: (value) => {
            return value + "px";
        },
    },

    /**
     * Padding Bottom
     */
    paddingBottom: {
        name: "padding",
        default: 0,
        get: (value) => {
            const [p1, , p3] = value.replace(/\s+/g, " ").split(" ");
            if (p3) {
                return parseInt(p3);
            }

            return parseInt(p1);
        },
        set: (value, current) => {
            value += "px";
            if (!current) {
                return `0px 0px ${value} 0px`;
            }

            const [p1, p2, p3, p4] = current.replace(/\s+/g, " ").split(" ");
            return p4
                ? `${p1} ${p2} ${value} ${p4}`
                : p3
                ? `${p1} ${p2} ${value}`
                : p2
                ? `${p1} ${p2} ${value} ${p2}`
                : `${p1} ${p1} ${value} ${p1}`;
        },
    },

    /**
     * Padding Left
     */
    paddingLeft: {
        name: "padding",
        default: 0,
        get: (value) => {
            const [p1, p2, p3, p4] = value.replace(/\s+/g, " ").split(" ");
            if (p4) {
                return parseInt(p4);
            } else if (p3 || p2) {
                return parseInt(p2);
            }

            return parseInt(p1);
        },
        set: (value, current) => {
            value += "px";
            if (!current) {
                return `0px 0px 0px ${value}`;
            }

            const [p1, p2, p3, p4] = current.replace(/\s+/g, " ").split(" ");
            return p3
                ? `${p1} ${p2} ${p3} ${value}`
                : p2
                ? `${p1} ${p2} ${p1} ${value}`
                : `${p1} ${p1} ${p1} ${value}`;
        },
    },

    /**
     * Padding Left and Right
     */
    paddingLeftRight: {
        name: "padding",
        default: 0,
        get: (value) => {
            const [p1, p2, p3, p4] = value.replace(/\s+/g, " ").split(" ");
            if (p4) {
                return p2 == p4 ? parseInt(p2) : "";
            } else if (p3 || p2) {
                return parseInt(p2);
            }

            return parseInt(p1);
        },
        set: (value, current) => {
            value += "px";
            if (!current) {
                return `0px ${value}`;
            }

            const [p1, , p3] = current.replace(/\s+/g, " ").split(" ");
            return p2 ? `${p1} ${value} ${p3} ${value}` : `${p1} ${value}`;
        },
    },

    /**
     * Padding Right
     */
    paddingRight: {
        name: "padding",
        default: 0,
        get: (value) => {
            const [p1, p2] = value.replace(/\s+/g, " ").split(" ");
            if (p2) {
                return parseInt(p2);
            }

            return parseInt(p1);
        },
        set: (value, current) => {
            value += "px";
            if (!current) {
                return `0px ${value} 0px 0px`;
            }

            const [p1, p2, p3, p4] = current.replace(/\s+/g, " ").split(" ");
            return p4
                ? `${p1} ${value} ${p3} ${p4}`
                : p3
                ? `${p1} ${value} ${p3} ${p2}`
                : p2
                ? `${p1} ${value} ${p1} ${p2}`
                : `${p1} ${value} ${p1} ${p1}`;
        },
    },

    /**
     * Padding Top
     */
    paddingTop: {
        name: "padding",
        default: 0,
        get: (value) => {
            const [p1] = value.replace(/\s+/g, " ").split(" ");
            return parseInt(p1);
        },
        set: (value, current) => {
            value += "px";
            if (!current) {
                return `${value} 0px 0px 0px`;
            }

            const [p1, p2, p3, p4] = current.replace(/\s+/g, " ").split(" ");
            return p4
                ? `${value} ${p2} ${p3} ${p4}`
                : p3
                ? `${value} ${p2} ${p3} ${p2}`
                : p2
                ? `${value} ${p2} ${p1} ${p2}`
                : `${value} ${p1} ${p1} ${p1}`;
        },
    },

    /**
     * Padding Top and Bottom
     */
    paddingTopBottom: {
        name: "padding",
        default: 0,
        get: (value) => {
            const [p1, , p3] = value.replace(/\s+/g, " ").split(" ");
            if (p3) {
                return p1 == p3 ? parseInt(p1) : "";
            }

            return parseInt(p1);
        },
        set: (value, current) => {
            value += "px";
            if (!current) {
                return `${value} 0px`;
            }

            const [p1, p2, p3, p4] = current.replace(/\s+/g, " ").split(" ");
            return p4
                ? `${value} ${p2} ${value} ${p4}`
                : p3
                ? `${value} ${p2} ${value}`
                : p2
                ? `${value} ${p2}`
                : `${value} ${p1}`;
        },
    },

    /**
     * Rotation
     */
    rotation: {
        name: "transform",
        default: 0,
        get: (value) => {
            const matches = value.match(/rotate\(([^)]+)\)/);
            return matches
                ? Math.round((parseFloat(matches[1]) * 18000) / Math.PI) / 100
                : 0;
        },
        set: (value) => `rotate(${(value * Math.PI) / 180}rad)`,
    },

    /**
     * Text Align Center
     */
    textAlignCenter: {
        name: "text-align",
        default: false,
        get: (value) => value === "center",
        set: (value) => (value ? "center" : "left"),
    },

    /**
     * Text Align Right
     */
    textAlignRight: {
        name: "text-align",
        default: false,
        get: (value) => value === "right",
        set: (value) => (value ? "right" : "left"),
    },

    /**
     * Top position
     */
    top: {
        name: "top",
        default: "",
        get: (value) => (value == "auto" ? "" : parseInt(value)),
        set: (value) => (value ? parseInt(value) + "px" : "auto"),
    },

    /**
     * Text underline
     */
    underline: {
        name: "text-decoration",
        default: false,
        get: (value) => value === "underline",
        set: (value) => (value ? "underline" : "none"),
    },

    /**
     * Text uppercase
     */
    uppercase: {
        name: "text-transform",
        default: false,
        get: (value) => value === "uppercase",
        set: (value) => (value ? "uppercase" : "none"),
    },

    /**
     * White Space No Wrap
     */
    whiteSpaceNormal: {
        name: "white-space",
        default: false,
        get: (value) => value === "normal",
        set: (value) => (value ? "normal" : "nowrap"),
    },

    /**
     * Width
     */
    width: {
        name: "width",
        default: "",
        get: (value) => (value == "auto" ? "" : parseInt(value)),
        set: (value) => (value ? parseInt(value) + "px" : "auto"),
    },
};
