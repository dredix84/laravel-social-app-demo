<template>
    <div>
        <h1><i class="fas fa-comments"></i> Posts</h1>
        <b-button
            v-b-modal.modal-edit-post
            v-b-tooltip.hover
            variant="success"
            title="Add new post"
        >
            <i class="fas fa-plus"></i> Create Post
        </b-button>
        <b-button
            v-b-tooltip.hover
            title="Refresh"
            @click="getPosts"
        >
            <i class="fas fa-sync"></i> Refresh
        </b-button>

        <div class="post-list">
            <b-row class="my-1">
                <b-col md="4" v-for="post in posts" :key="post.key">
                    <b-card
                        :title="post.title"
                        :img-src="post.image_url"
                        :img-alt="post.title"
                        img-top
                        tag="article"
                        style="max-width: 20rem;"
                        class="mb-6"
                    >
                        <b-card-text>
                            <div class="post-body" v-html="nl2br(post.body)">
                            </div>
                            <div class="post-date">
                                <small>{{post.created_at | moment("ddd, MMM Do YYYY")}}</small>
                            </div>
                        </b-card-text>

                        <b-card-text>
                            <b-list-group flush>
                                <b-list-group-item
                                    v-for="comment in post.comments"
                                    :key="comment.id"
                                    class="d-flex justify-content-between align-items-center"
                                >
                                    <p>
                                        <b-badge variant="primary" v-b-tooltip.hover title="Likes">
                                            <i class="fas fa-thumbs-up"></i> 0
                                        </b-badge>

                                        <span
                                            v-b-tooltip.hover
                                            :title="comment.created_at | moment('ddd, MMM Do YYYY')"
                                            v-html="nl2br(comment.body)"
                                        >

                                        </span>
                                    </p>
                                    <b-button
                                        size="sm"
                                        variant="outline-danger"
                                        v-b-tooltip.hover
                                        title="Remove comment"
                                        @click="removeComment(post, comment)"
                                        v-if="allowCommentDelete(comment)"
                                    >X
                                    </b-button>
                                </b-list-group-item>
                            </b-list-group>
                        </b-card-text>

                        <template v-slot:footer>
                            <b-row class="my-1">
                                <b-col md="6">
                                    <b-badge variant="primary">{{post.comments.length}} Comments</b-badge>
                                </b-col>
                                <b-col md="6">
                                    <b-button
                                        @click="showSaveCommentModal(post)"
                                        variant="success"
                                        v-b-tooltip.hover
                                        :title="'Add comment for ' + post.title"
                                    >
                                        <i class="fas fa-plus-square"></i> Comment
                                    </b-button>
                                </b-col>
                            </b-row>
                        </template>
                    </b-card>
                </b-col>
            </b-row>

        </div>

        <b-modal id="modal-edit-post" title="Create Post" @ok="savePost" ok-title="Save Post" hide-footer>
            <form @submit.prevent="savePost">
                <b-container fluid>
                    <b-row class="my-1">
                        <b-col sm="3">
                            <label>Title:</label>
                        </b-col>
                        <b-col sm="9">
                            <b-form-input
                                v-model="newPost.title"
                                type="text"
                                placeholder="Title"
                                name="title"
                                required
                                :class="{ 'is-invalid': newPost.errors.has('title') }"
                            />
                            <has-error :form="newPost" field="title"></has-error>
                        </b-col>
                    </b-row>
                    <b-row class="my-1">
                        <b-col sm="3">
                            <label>Image (URL):</label>
                        </b-col>
                        <b-col sm="9">
                            <b-form-input
                                v-model="newPost.image_url"
                                type="url"
                                placeholder="Image"
                                name="image_url"
                                :class="{ 'is-invalid': newPost.errors.has('image_url') }"
                            />
                            <has-error :form="newPost" field="image_url"></has-error>
                        </b-col>
                    </b-row>
                    <b-row class="my-1">
                        <b-col sm="3">
                            <label>Body:</label>
                        </b-col>
                        <b-col sm="9">
                            <b-form-textarea
                                v-model="newPost.body"
                                placeholder="Your post body here..."
                                rows="3"
                                max-rows="6"
                                name="body"
                                required
                                :class="{ 'is-invalid': newPost.errors.has('body') }"
                            ></b-form-textarea>
                            <has-error :form="newPost" field="body"></has-error>
                        </b-col>
                    </b-row>
                    <hr/>
                    <b-row class="my-1">
                        <b-col sm="6">
                            <b-button class="mt-3" variant="outline-danger" block @click="hideSavePostModal">Close
                            </b-button>
                        </b-col>
                        <b-col sm="6">
                            <b-button class="mt-3" variant="success" block @click="savePost">Save Post
                            </b-button>
                        </b-col>
                    </b-row>
                </b-container>
            </form>
        </b-modal>

        <b-modal id="modal-edit-comment" title="Add Comment" @ok="saveComment" hide-footer>
            <form @submit.prevent="saveComment">
                <b-container fluid>
                    <b-row class="my-1">
                        <b-col md="12">
                            <b-form-textarea
                                v-model="newComment.body"
                                placeholder="Your comment..."
                                rows="3"
                                max-rows="6"
                                name="body"
                                required
                                :class="{ 'is-invalid': newComment.errors.has('body') }"
                            ></b-form-textarea>
                            <has-error :form="newComment" field="body"></has-error>
                        </b-col>
                    </b-row>
                    <hr/>
                    <b-row class="my-1">
                        <b-col sm="6">
                            <b-button class="mt-3" variant="outline-danger" block @click="hideSaveCommentModal">Close
                            </b-button>
                        </b-col>
                        <b-col sm="6">
                            <b-button class="mt-3" variant="success" block @click="saveComment">Save Comment
                            </b-button>
                        </b-col>
                    </b-row>
                </b-container>
            </form>
        </b-modal>

    </div>
</template>

<script>
    export default {
        name: "posts",
        data() {
            return {
                posts: [],
                newPost: new Form({
                    id: null,
                    title: '',
                    image_url: '',
                    body: ''
                }),
                newComment: new Form({
                    id: null,
                    post_id: null,
                    body: '',
                    key: ''
                }),
                workingPost: null,
                userId: USER_ID
            }
        },
        methods: {
            hideSavePostModal: function () {
                this.$bvModal.hide('modal-edit-post');
            },
            hideSaveCommentModal: function () {
                this.$bvModal.hide('modal-edit-comment');
            },
            showSaveCommentModal: function (post) {
                this.workingPost = post;
                this.$bvModal.show('modal-edit-comment');
            },
            getPosts: function () {
                var that = this;
                axios.get('/api/post')
                    .then(function ({data}) {
                        that.posts = data;
                    })
                    .catch(err => console.error(err));
            },
            savePost: function () {
                var that = this;
                this.newPost.post('/api/post')
                    .then(({data}) => {
                        console.log(data);
                        that.newPost.reset();
                        that.getPosts();
                        that.hideSavePostModal();
                    })
                    .catch(err => console.error(err));
            },
            saveComment: function () {
                var that = this;
                this.newComment.post_id = this.workingPost.id;
                this.newComment.post('/api/comment')
                    .then(({data}) => {
                        console.log(data);
                        that.workingPost.comments.unshift(data);
                        that.newComment.reset();
                        that.hideSaveCommentModal();
                    })
                    .catch(err => console.error(err));
            },
            removeComment: function (post, comment) {
                this.newComment.post_id = post.id;
                this.newComment.id = comment.id;
                var that = this;

                this.$swal.fire({
                    title: 'Are you sure?',
                    text: "Are you sure you want to remove this comment?",
                    type: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.value) {
                        that.newComment.delete('/api/comment/' + comment.id)
                            .then(({data}) => {
                                console.log(data);

                                var index = post.comments.indexOf(comment);
                                if (index > -1) {
                                    post.comments.splice(index, 1);
                                }
                            })
                            .catch(err => console.error(err));
                    }
                });
            },
            nl2br: function (str, is_xhtml) {
                if (typeof str === 'undefined' || str === null) {
                    return '';
                }
                var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
                return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
            },
            allowCommentDelete: function (comment) {
                return (comment.user_id == this.userId);
            }
        },
        created() {
            this.getPosts();
        },
        filters: {}
    }
</script>

<style scoped>

</style>
