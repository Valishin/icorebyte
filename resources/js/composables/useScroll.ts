export const useScroll = () => {
    const scrollToNextSection = (selector: string) => {
        const node = document.querySelector(selector) as HTMLElement | null;
        const nextSection = node?.nextElementSibling as HTMLElement | null;

        if (nextSection) {
            nextSection.scrollIntoView({
                behavior: "smooth",
                block: "start",
            });
            return;
        }

        // fallback si no hay siguiente sección
        window.scrollTo({
            top: window.scrollY + window.innerHeight * 0.9,
            behavior: "smooth",
        });
    };

    return {
        scrollToNextSection,
    };
};
