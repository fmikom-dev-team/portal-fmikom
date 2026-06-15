export const formatDateForInput = (dateString: string | null): string => {
    if (!dateString) {
        return "";
    }

    const d = new Date(dateString);

    return isNaN(d.getTime()) ? "" : d.toISOString().split("T")[0];
};
