import "./editorial.js";

// Quill is heavy and only the admin post editor needs it.
window.initPostEditor = async ($wire) => {
    const { initPostEditor } = await import("./post-editor.js");

    initPostEditor($wire);
};

// Cropper.js only loads once a profile photo is picked; only the cropped result is uploaded.
window.photoCropper = () => {
    // Kept out of Alpine's reactive state: the cropper holds DOM nodes, which Alpine would try to proxy.
    let session = null;

    return {
        cropping: false,
        ready: false,
        busy: false,
        error: "",

        async pick(event) {
            const file = event.target.files[0];

            if (!file) {
                return;
            }

            // Cleared so the same file can be picked again. Flux fires change once more for it, and the guard above stops there.
            event.target.value = "";
            this.error = "";
            this.close();
            this.cropping = true;
            await this.$nextTick();

            try {
                const { openPhotoCropper } = await import("./photo-cropper.js");
                session = await openPhotoCropper(file, this.$refs.stage);
                this.ready = true;
            } catch {
                this.close();
                this.error = "This image could not be opened here. Use a JPG, PNG or WebP file.";
            }
        },

        async apply() {
            this.busy = true;

            try {
                const blob = await session.toBlob();
                const photo = new File([blob], "profile-photo.jpg", { type: "image/jpeg" });

                await new Promise((resolve, reject) => this.$wire.upload("photo", photo, resolve, reject));
                this.close();
            } catch {
                this.error = "The cropped photo could not be uploaded. Try again.";
            } finally {
                this.busy = false;
            }
        },

        close() {
            session?.destroy();
            session = null;
            this.ready = false;
            this.cropping = false;
        },
    };
};
