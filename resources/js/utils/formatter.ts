export const formatCurrency = (
    value: number | string | null | undefined,
    prefix = "Rp"
): string => {
    if (value === null || value === undefined || value === "") {
        return `${prefix} 0`;
    }

    return `${prefix} ${Number(value).toLocaleString("id-ID")}`;
};

export const formatNumber = (
    value: number | string | null | undefined
): string => {
    if (value === null || value === undefined || value === "") {
        return "0";
    }

    return Number(value).toLocaleString("id-ID");
};

export const parseNumber = (
    value: string | null | undefined
): number => {
    if (!value) return 0;

    return Number(value.replace(/\./g, ""));
};