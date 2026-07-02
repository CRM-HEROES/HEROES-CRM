import { ref, onMounted, onUnmounted } from "vue";

export function useDrag(element) {
    const event = ref(null);
    const dragging = ref(false);
    const origin = ref(null);
    const position = ref(null);

    function updateOrigin(ev) {
        origin.value = {
            x: ev.clientX,
            y: ev.clientY,
        };
    }

    function updatePosition(ev) {
        event.value = ev;
        position.value = {
            x: ev.clientX,
            y: ev.clientY,
        };
    }

    function mousedown(ev) {
        event.value = ev;
        dragging.value = true;
        updateOrigin(ev);
        updatePosition(ev);
        window.addEventListener("mousemove", mousemove);
        window.addEventListener("mouseup", mouseup);
        ev.stopPropagation();
        ev.preventDefault();
    }

    function touchstart(e) {
        e.stopPropagation();
        e.preventDefault();
        const ev = e.touches[0];
        event.value = ev;
        dragging.value = true;
        updateOrigin(ev);
        updatePosition(ev);
        window.addEventListener("touchmove", touchmove, { passive: false });
        window.addEventListener("touchend", touchend, { passive: false });
    }

    function mousemove(event) {
        event.stopPropagation();
        event.preventDefault();
        updatePosition(event);
    }

    function touchmove(event) {
        event.stopPropagation();
        event.preventDefault();
        updatePosition(event.touches[0]);
    }

    function mouseup(event) {
        dragging.value = false;
        window.removeEventListener("mousemove", mousemove);
        window.removeEventListener("mouseup", mouseup);
    }

    function touchend(event) {
        event.stopPropagation();
        event.preventDefault();
        dragging.value = false;
        window.removeEventListener("touchmove", touchmove);
        window.removeEventListener("touchend", touchend);
    }

    element.addEventListener("mousedown", mousedown);
    element.addEventListener("touchstart", touchstart, { passive: false });

    onUnmounted(() => {
        element.removeEventListener("mousedown", mousedown);
        element.removeEventListener("touchstart", mousedown);
    });

    // expose managed state as return value
    return { event, dragging, origin, position };
}
