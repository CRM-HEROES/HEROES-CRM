import IncomingCommunicationService from "@/apis/project/incoming-communication";
import EventBus from "@/utils/event-bus";

export const COMMUNICATIONS_READ = "incoming-communications-read";

export async function markCommunicationsRead(projectSlug, prospectId, type, records) {
    const ids = [...new Set((records || []).map(record => Number(record.id))
        .filter(id => Number.isSafeInteger(id) && id > 0))];
    if (!projectSlug || !prospectId || !ids.length) {
        return false;
    }

    try {
        let count;
        for (let offset = 0; offset < ids.length; offset += 500) {
            const { data } = await IncomingCommunicationService.read(projectSlug, prospectId, type, ids.slice(offset, offset + 500));
            count = data.count;
        }
        EventBus.emit(COMMUNICATIONS_READ, { projectSlug, count });
        return true;
    } catch (error) {
        // Reading remains available; a later consultation can retry the receipt.
        return false;
    }
}
