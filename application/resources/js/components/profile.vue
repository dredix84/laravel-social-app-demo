<template>
    <div>
        <h1>Profile</h1>
        <b-card
            :title="name"
            img-src="https://picsum.photos/600/300/?image=23"
            img-alt="Image"
            img-top
            tag="article"
            style="max-width: 20rem;"
            class="mb-6"
        >
            <b-card-text>
                ID: {{id}}<br>
                Email: {{email}}<br>
            </b-card-text>

        </b-card>
    </div>
</template>

<script>
    export default {
        name: "profile",
        data() {
            return {
                id: USER_ID,
                name: USER_NAME,
                email: USER_EMAIL,
            }
        }
    }
</script>

<style scoped>

</style>
