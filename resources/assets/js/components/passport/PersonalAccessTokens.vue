<style scoped>
    .action-link {
        cursor: pointer;
    }

    .m-b-none {
        margin-bottom: 0;
    }
</style>

<template>
    <div>
        <div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span>
                            {{ $t('passport.personal_access_tokens') }}
                        </span>

                        <a class="action-link" @click="showCreateTokenForm">
                            {{ $t('passport.create_new_token') }}
                        </a>
                    </div>
                </div>

                <div class="panel-body">
                    <!-- No Tokens Notice -->
                    <p class="m-b-none" v-if="tokens.length === 0">
                        {{ $t('passport.you_have_not_created_any_personal_access_tokens') }}
                    </p>

                    <!-- Personal Access Tokens -->
                    <table class="table table-borderless m-b-none" v-if="tokens.length > 0">
                        <thead>
                            <tr>
                                <th>{{ $t('passport.name') }}</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="token in tokens">
                                <!-- Client Name -->
                                <td style="vertical-align: middle;">
                                    {{ token.name }}
                                </td>

                                <!-- Delete Button -->
                                <td style="vertical-align: middle;">
                                    <a class="action-link text-danger" @click="revoke(token)">
                                        {{ $t('passport.delete') }}
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Create Token Modal -->
        <div class="modal fade" id="modal-create-token" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button " class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

                        <h4 class="modal-title">
                            {{ $t('passport.create_token') }}
                        </h4>
                    </div>

                    <div class="modal-body">
                        <!-- Form Errors -->
                        <div class="alert alert-danger" v-if="form.errors.length > 0">
                            <p>{{ $t('passport.something_went_wrong') }}</p>
                            <br>
                            <ul>
                                <li v-for="error in form.errors">
                                    {{ error }}
                                </li>
                            </ul>
                        </div>

                        <!-- Create Token Form -->
                        <form class="form-horizontal" role="form" @submit.prevent="store">
                            <!-- Name -->
                            <div class="form-group">
                                <label class="col-md-4 control-label">{{ $t('passport.name') }}</label>

                                <div class="col-md-6">
                                    <input id="create-token-name" type="text" class="form-control" name="name" v-model="form.name">
                                </div>
                            </div>

                            <!-- Scopes -->
                            <div class="form-group" v-if="scopes.length > 0">
                                <label class="col-md-4 control-label">{{ $t('passport.scopes') }}</label>

                                <div class="col-md-6">
                                    <div v-for="scope in scopes">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox"
                                                    @click="toggleScope(scope.id)"
                                                    :checked="scopeIsAssigned(scope.id)">

                                                    {{ scope.id }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ $t('passport.close') }}</button>

                        <button type="button" class="btn btn-primary" @click="store">
                            {{ $t('passport.create') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Access Token Modal -->
        <div class="modal fade" id="modal-access-token" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

                        <h4 class="modal-title">
                            {{ $t('passport.personal_access_token') }}
                        </h4>
                    </div>

                    <div class="modal-body">
                        <p>
                            {{ $t('passport.new_personal_token_info') }}
                        </p>

                        <pre><code>{{ accessToken }}</code></pre>
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ $t('passport.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import _ from 'lodash'
    export default {
        /*
         * The component's data.
         */
        data() {
            return {
                accessToken: null,

                tokens: [],
                scopes: [],

                form: {
                    name: '',
                    scopes: [],
                    errors: []
                }
            };
        },

        /**
         * Prepare the component (Vue 1.x).
         */
        ready() {
            this.prepareComponent();
        },

        /**
         * Prepare the component (Vue 2.x).
         */
        mounted() {
            this.prepareComponent();
        },

        methods: {
            /**
             * Prepare the component.
             */
            prepareComponent() {
                this.getTokens();
                this.getScopes();

                $('#modal-create-token').on('shown.bs.modal', () => {
                    $('#create-token-name').focus();
                });
            },

            /**
             * Get all of the personal access tokens for the user.
             */
            getTokens() {
                axios.get('/oauth/personal-access-tokens')
                        .then(response => {
                            this.tokens = response.data;
                        });
            },

            /**
             * Get all of the available scopes.
             */
            getScopes() {
                axios.get('/oauth/scopes')
                        .then(response => {
                            this.scopes = response.data;
                        });
            },

            /**
             * Show the form for creating new tokens.
             */
            showCreateTokenForm() {
                $('#modal-create-token').appendTo('body').modal('show');
            },

            /**
             * Create a new personal access token.
             */
            store() {
                this.accessToken = null;

                this.form.errors = [];

                axios.post('/oauth/personal-access-tokens', this.form)
                        .then(response => {
                            this.form.name = '';
                            this.form.scopes = [];
                            this.form.errors = [];

                            this.tokens.push(response.data.token);

                            this.showAccessToken(response.data.accessToken);
                        })
                        .catch(error => {
                            if (typeof error.response.data === 'object') {
                                this.form.errors = _.flatten(_.toArray(error.response.data));
                            } else {
                                this.form.errors = [$t('passport.something_went_wrong_please_try_again')];
                            }
                        });
            },

            /**
             * Toggle the given scope in the list of assigned scopes.
             */
            toggleScope(scope) {
                if (this.scopeIsAssigned(scope)) {
                    this.form.scopes = _.reject(this.form.scopes, s => s == scope);
                } else {
                    this.form.scopes.push(scope);
                }
            },

            /**
             * Determine if the given scope has been assigned to the token.
             */
            scopeIsAssigned(scope) {
                return _.indexOf(this.form.scopes, scope) >= 0;
            },

            /**
             * Show the given access token to the user.
             */
            showAccessToken(accessToken) {
                $('#modal-create-token').modal('hide');

                this.accessToken = accessToken;

                $('#modal-access-token').appendTo('body').modal('show');
            },

            /**
             * Revoke the given token.
             */
            revoke(token) {
                axios.delete('/oauth/personal-access-tokens/' + token.id)
                        .then(response => {
                            this.getTokens();
                        });
            }
        }
    }
</script>
