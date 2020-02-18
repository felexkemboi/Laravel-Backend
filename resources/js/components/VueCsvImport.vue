<!--<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-default">
                    <div class="card-header">Example Component</div>

                    <div class="card-body">
                        I'm an example component.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    name :"VueCsvImport"
    export default {
        mounted() {
            console.log('Component mounted.')
        }
    }
</script> -->

<template>
    <div class="vue-csv-uploader">
        <div class="form">
            <div class="vue-csv-uploader-part-one">
                <div class="form-group csv-import-file">
                    <input ref="csv" type="file" @change.prevent="validFileMimeType" :class="inputClass" name="csv">
                    <slot name="error" v-if="showErrorMessage">
                        <div class="invalid-feedback d-block">
                            File type is invalid
                        </div>
                    </slot>
                </div>
                <div class="form-group">
                    <slot name="next" :load="load">
                        <button type="submit" :disabled="disabledNextButton" :class="buttonClass" @click.prevent="load">
                            {{ loadBtnText }}
                        </button>
                    </slot>
                </div>
            </div>
            <div class="vue-csv-uploader-part-two">
                <div class="vue-csv-mapping" v-if="sample">
                    <table :class="tableClass">
                        <slot name="thead">
                            <thead>
                            <tr>
                                <th>Field</th>
                                <th>CSV Column</th>
                            </tr>
                            </thead>
                        </slot>
                        <tbody>
                        <tr v-for="(field, key) in fieldsToMap" :key="key">
                            <td>{{ field.label }}</td>
                            <td>
                                <select class="form-control" :name="`csv_uploader_map_${key}`" v-model="map[field.key]" @input="handleValueChange">
                                    <option v-for="(column, key) in options" :key="key" :value="key" :disabled="!!isUsed[key]">{{ column }}</option>
                                </select>
                            </td>
                        </tr>
                        </tbody>
                        <button type="submit" :disabled="disabledNextButton" :class="buttonClass" @click.prevent="chekthis">
                            {{ chekiBtnText }}
                        </button>
                    </table>
                    <div class="mt-2">
                          {{ data }}
                    </div>
                    <div class="form-group" v-if="url">
                        <slot name="submit" :submit="submit">
                            <input type="submit" :class="buttonClass" @click.prevent="submit" :value="submitBtnText">
                        </slot>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import _ from 'lodash';
    import axios from 'axios';
    import Papa from 'papaparse';
    import mimeTypes from "mime-types";

    export default {
        props: {
            value: Array,
            url: {
                type: String
            },
            mapFields: {
                required: false //true
            },
            callback: {
                type: Function,
                default: () => ({})
            },
            catch: {
                type: Function,
                default: () => ({})
            },
            finally: {
                type: Function,
                default: () => ({})
            },
            parseConfig: {
                type: Object,
                default() {
                    return {};
                }
            },
            headers: {
                default: null
            },
            loadBtnText: {
                type: String,
                default: "Next"
            },
            chekiBtnText: {
                type: String,
                default: "Submit"
            },
            submitBtnText: {
                type: String,
                default: "Submit"
            },
            tableClass: {
                type: String,
                default: "table"
            },
            checkboxClass: {
                type: String,
                default: "form-check-input"
            },
            buttonClass: {
                type: String,
                default: "btn btn-primary"
            },
            inputClass: {
                type: String,
                default: "form-control-file"
            },
            validation: {
                type: Boolean,
                default: true,
            },
            fileMimeTypes: {
                type: Array,
                default: () => {
                    return ["text/csv", "text/x-csv", "application/vnd.ms-excel", "text/plain"];
                }
            }
        },

        data: () => ({
            form: {
                csv: null,
            },
            fieldsToMap: [],
            map: {},
            hasHeaders: true,
            csv: null,
            sample: null,
            isValidFileMimeType: false,
            fileSelected: false,
            data:null,
            options:[],
            isUsed: {}

        }),

        created() {
            /*
            This is where we decide if the file has headers or not
            defualt being true....
            */
            //this.hasHeaders = this.headers;




            if (_.isArray(this.mapFields)) {
                this.fieldsToMap = _.map(this.mapFields, (item) => {
                    return {
                        key: item,
                        label: item
                    };
                });
            } else {
                this.fieldsToMap = _.map(this.mapFields, (label, key) => {
                    return {
                        key: key,
                        label: label
                    };
                });
            }
        },

        methods: {
            submit() {
                const _this = this;
                //this.url = "http://127.0.0.1:5000/vue" // add the url to recir
                this.form.csv = this.buildMappedCsv();
                if (this.url) {
                    axios.post(this.url, this.form).then(response => {
                        _this.callback(response);
                    }).catch(response => {
                        _this.catch(response);
                    }).finally(response => {
                        _this.finally(response);
                    });
                } else {
                    _this.callback(this.form.csv);
                }
            },
            onComplete: function(){
              alert('Yay. Done!');
            },
            chekthis(){
              const _this = this;
              //this.$emit('input', this.form.csv);
              //console.log(this.form.csv);
              console.log('The number of items in my CSV is ' + this.form.csv.length);

              axios.post('http://127.0.0.1:8000/api/csv', this.form.csv)
              .then((response) => {
                  this.data = response
                  console.log("Data sent to http://127.0.0.1:8000/api/csv ")
                  this.data = this.form.csv
                  //console.log(this.form.csv)
                  //this.data = response.data
                  console.log(response.data)
                  console.log("This was successfully done")
                })
                .catch(err => { console.log(err)});
            },




            buildMappedCsv() {
                const _this = this;

                let csv = this.hasHeaders ? _.drop(this.csv) : this.csv;

                return _.map(csv, (row) => {
                    let newRow = {};

                    _.forEach(_this.map, (column, field) => {
                        _.set(newRow, field, _.get(row, column));
                    });

                    return newRow;
                });
            },
            validFileMimeType() {
                let file = this.$refs.csv.files[0];
                const mimeType = file.type === "" ? mimeTypes.lookup(file.name) : file.type;

                if (file) {
                    this.fileSelected = true;
                    this.isValidFileMimeType = this.validation ? this.validateMimeType(mimeType) : true;
                } else {
                    this.isValidFileMimeType = !this.validation;
                    this.fileSelected = false;
                }
            },
            validateMimeType(type) {
                return this.fileMimeTypes.indexOf(type) > -1;
            },
            load() {
                const _this = this;

                this.readFile((output) => {
                    _this.sample = _.get(Papa.parse(output, { preview: 2, skipEmptyLines: true }), "data");
                    _this.csv = _.get(Papa.parse(output, { skipEmptyLines: true }), "data");

                    //console.log(_this.sample[0]);

                    for(let i = 0; i < _this.sample[0].length; i++){
                      console.log(_this.sample[0][i]);
                      this.options.push(_this.sample[0][i]);
                    }

                    //console.log(this.options)

                });
            },
            readFile(callback) {
                let file = this.$refs.csv.files[0];

                if (file) {
                    let reader = new FileReader();
                    reader.readAsText(file, "UTF-8");
                    reader.onload = function (evt) {
                        callback(evt.target.result);
                    };
                    reader.onerror = function () {
                    };
                }
            },
            toggleHasHeaders() {
                this.hasHeaders = !this.hasHeaders;
            },
            makeId(id) {
                return `${id}${this._uid}`;
            },
            handleValueChange(e) {
              const option = e.target.value
              // do your smart logic here
              // bra, bra, bra,,,,
              // store click history at the end
              this.isUsed = {
                ...this.isUsed,[option]: true
              };
            }
        },
        watch: {
            map: {
                deep: true,
                handler: function (newVal) {
                    if (!this.url) {
                        /*let hasAllKeys = Array.isArray(this.mapFields) ? _.every(this.mapFields, function (item) {
                            return newVal.hasOwnProperty(item);
                        }) : _.every(this.mapFields, function (item, key) {
                            return newVal.hasOwnProperty(key);
                        });

                        if (hasAllKeys) {
                            this.submit();
                        }*/
                        this.submit();
                    }
                }
            }
        },
        computed: {
            firstRow() {
                return _.get(this, "sample.0");
            },
            usedItems() {
              const vm = this;
              return this.options.filter(o => !!vm.isUsed[o])
            },
            showErrorMessage() {
                return this.fileSelected && !this.isValidFileMimeType;
            },
            disabledNextButton() {
                return !this.isValidFileMimeType;
            }
        },
    };
</script>

<style>
    #app {
        font-family: "Avenir", Helvetica, Arial, sans-serif;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        text-align: center;
        color: #2c3e50;
        margin-top: 60px;
    }

    .container {
        text-align: left;
    }

    pre code {
        background-color: #eee;
        border: 1px solid #999;
        display: block;
        padding: 20px;
    }

    #app .form {
        text-align: left;
    }
    .vue-form-wizard .wizard-btn{display:inline-block;margin-bottom:0;font-weight:400;text-align:center;vertical-align:middle;-ms-touch-action:manipulation;touch-action:manipulation;cursor:pointer;background-image:none;border:1px solid transparent;white-space:nowrap;padding:6px 12px;font-size:14px;line-height:1.42857;border-radius:4px}.vue-form-wizard .wizard-btn.disabled,.vue-form-wizard .wizard-btn[disabled],fieldset[disabled] .vue-form-wizard .wizard-btn{cursor:not-allowed;opacity:.65;filter:alpha(opacity=65);-webkit-box-shadow:none;box-shadow:none}.vue-form-wizard *{-webkit-box-sizing:border-box;box-sizing:border-box}.vue-form-wizard a{text-decoration:none}.vue-form-wizard .wizard-nav{margin-bottom:0;padding-left:0;list-style:none}.vue-form-wizard .wizard-nav>li{position:relative;display:block}.vue-form-wizard .wizard-nav>li>a{position:relative;display:block;padding:10px 15px}.vue-form-wizard .wizard-nav>li>a:focus,.vue-form-wizard .wizard-nav>li>a:hover{text-decoration:none;background-color:#eee}.vue-form-wizard .wizard-nav>li.disabled>a{color:#777}.vue-form-wizard .wizard-nav>li.disabled>a:focus,.vue-form-wizard .wizard-nav>li.disabled>a:hover{color:#777;text-decoration:none;background-color:transparent;cursor:not-allowed}.vue-form-wizard .wizard-progress-bar{float:left;width:0;height:100%;font-size:12px;line-height:20px;color:#fff;text-align:center;background-color:#337ab7;-webkit-box-shadow:inset 0 -1px 0 rgba(0,0,0,.15);box-shadow:inset 0 -1px 0 rgba(0,0,0,.15);-webkit-transition:width .6s ease;transition:width .6s ease}.vue-form-wizard .navbar .navbar-nav>li>a.wizard-btn,.vue-form-wizard .wizard-btn{-webkit-box-sizing:border-box;box-sizing:border-box;border-width:2px;background-color:transparent;font-size:14px;font-weight:600;padding:6px 12px;min-width:140px}.vue-form-wizard .navbar .navbar-nav>li>a.wizard-btn:focus,.vue-form-wizard .navbar .navbar-nav>li>a.wizard-btn:hover,.vue-form-wizard .wizard-btn:focus,.vue-form-wizard .wizard-btn:hover{outline:0!important}.vue-form-wizard .wizard-nav-pills{margin-top:0;position:relative;text-align:center;display:-webkit-box;display:-ms-flexbox;display:flex;-ms-flex-wrap:wrap;flex-wrap:wrap}.vue-form-wizard .wizard-nav-pills a,.vue-form-wizard .wizard-nav-pills li{-webkit-box-flex:1;-ms-flex:1;flex:1;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-ms-flex-wrap:wrap;flex-wrap:wrap;-ms-flex-positive:1;flex-grow:1}.vue-form-wizard .wizard-nav-pills>li>a,.vue-form-wizard .wizard-nav-pills a{display:-webkit-box;display:-ms-flexbox;display:flex}.vue-form-wizard .wizard-nav-pills>li>a{-webkit-box-orient:vertical;-webkit-box-direction:normal;-ms-flex-direction:column;flex-direction:column;padding:0;margin:0 auto;color:rgba(0,0,0,.2);position:relative;top:3px}.vue-form-wizard .wizard-nav-pills>li>a:focus,.vue-form-wizard .wizard-nav-pills>li>a:hover{background-color:transparent;color:rgba(0,0,0,.2);outline:0!important}.vue-form-wizard .wizard-nav-pills>li>a.disabled{pointer-events:none;cursor:default}.vue-form-wizard .wizard-nav-pills>li.active>a,.vue-form-wizard .wizard-nav-pills>li.active>a:focus,.vue-form-wizard .wizard-nav-pills>li.active>a:hover{background-color:transparent;-webkit-transition:font-size .2s linear;transition:font-size .2s linear}.vue-form-wizard .wizard-nav-pills>li.active>a .wizard-icon,.vue-form-wizard .wizard-nav-pills>li.active>a:focus .wizard-icon,.vue-form-wizard .wizard-nav-pills>li.active>a:hover .wizard-icon{color:#fff;font-size:24px;display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-webkit-box-pack:center;-ms-flex-pack:center;justify-content:center;-webkit-transition:all .2s linear;transition:all .2s linear}.vue-form-wizard{padding-bottom:20px}.vue-form-wizard .is_error{border-color:#c84513!important}.vue-form-wizard .is_error .icon-container{background:#c84513!important}.vue-form-wizard.xs .wizard-icon-circle{width:40px;height:40px;font-size:16px}.vue-form-wizard.xs .wizard-icon-circle.tab_shape{height:25px}.vue-form-wizard.xs .wizard-nav-pills>li.active>a .wizard-icon{font-size:16px}.vue-form-wizard.xs .wizard-navigation .wizard-progress-with-circle{position:relative;top:25px;height:4px}.vue-form-wizard.sm .wizard-icon-circle{width:50px;height:50px;font-size:20px}.vue-form-wizard.sm .wizard-icon-circle.tab_shape{height:30px}.vue-form-wizard.sm .wizard-nav-pills>li.active>a .wizard-icon{font-size:20px}.vue-form-wizard.sm .wizard-navigation .wizard-progress-with-circle{position:relative;top:30px;height:4px}.vue-form-wizard.md .wizard-icon-circle{width:70px;height:70px;font-size:24px}.vue-form-wizard.md .wizard-icon-circle.tab_shape{height:40px}.vue-form-wizard.md .wizard-nav-pills>li.active>a .wizard-icon{font-size:24px}.vue-form-wizard.md .wizard-navigation .wizard-progress-with-circle{position:relative;top:40px;height:4px}.vue-form-wizard.lg .wizard-icon-circle{width:90px;height:90px;font-size:28px}.vue-form-wizard.lg .wizard-icon-circle.tab_shape{height:50px}.vue-form-wizard.lg .wizard-nav-pills>li.active>a .wizard-icon{font-size:28px}.vue-form-wizard.lg .wizard-navigation .wizard-progress-with-circle{position:relative;top:50px;height:4px}.vue-form-wizard .wizard-icon-circle{font-size:18px;border:3px solid #f3f2ee;border-radius:50%;font-weight:600;width:70px;height:70px;background-color:#fff;position:relative;display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-pack:center;-ms-flex-pack:center;justify-content:center;-ms-flex-line-pack:center;align-content:center}.vue-form-wizard .wizard-icon-circle.square_shape{border-radius:0}.vue-form-wizard .wizard-icon-circle.tab_shape{width:100%;min-width:100px;height:40px;border:none;background-color:#f3f2ee;border-radius:0}.vue-form-wizard .wizard-icon-circle .wizard-icon-container{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-pack:center;-ms-flex-pack:center;justify-content:center;-webkit-box-flex:1;-ms-flex:1;flex:1;border-radius:50%;margin:-3px}.vue-form-wizard .wizard-icon-circle .wizard-icon-container.square_shape{border-radius:0}.vue-form-wizard .wizard-icon-circle .wizard-icon-container.tab_shape{border-radius:0;margin:0}.vue-form-wizard .wizard-icon-circle .wizard-icon{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-webkit-box-pack:center;-ms-flex-pack:center;justify-content:center}.vue-form-wizard .wizard-tab-content{min-height:100px;padding:30px 20px 10px}.vue-form-wizard .wizard-header{padding:15px;position:relative;border-radius:3px 3px 0 0;text-align:center}.vue-form-wizard .wizard-title{color:#252422;font-weight:300;margin:0;text-align:center}.vue-form-wizard .category{font-size:14px;font-weight:400;color:#9a9a9a;margin-bottom:0;text-align:center}.vue-form-wizard .wizard-navigation .wizard-progress-with-circle{position:relative;top:40px;height:4px}.vue-form-wizard .wizard-navigation .wizard-progress-with-circle .wizard-progress-bar{-webkit-box-shadow:none;box-shadow:none;-webkit-transition:width .3s ease;transition:width .3s ease}.vue-form-wizard .clearfix:after{content:"";clear:both;display:table}.vue-form-wizard .wizard-card-footer{padding:0 20px}.vue-form-wizard .wizard-card-footer .wizard-footer-left{float:left}.vue-form-wizard .wizard-card-footer .wizard-footer-right{float:right}@media screen and (max-width:350px){.vue-form-wizard .wizard-card-footer{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-pack:center;-ms-flex-pack:center;justify-content:center;-webkit-box-orient:vertical;-webkit-box-direction:normal;-ms-flex-direction:column;flex-direction:column}.vue-form-wizard .wizard-card-footer .wizard-footer-left,.vue-form-wizard .wizard-card-footer .wizard-footer-right{float:none;-webkit-box-flex:1;-ms-flex:1;flex:1;display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-pack:center;-ms-flex-pack:center;justify-content:center}.vue-form-wizard .wizard-card-footer .wizard-footer-right button{margin-top:10px}}.vue-form-wizard.vertical .wizard-card-footer{display:block}.vue-form-wizard.vertical .wizard-nav-pills{-webkit-box-orient:vertical;-webkit-box-direction:normal;-ms-flex-direction:column;flex-direction:column}.vue-form-wizard.vertical .wizard-navigation{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-ms-flex-direction:row;flex-direction:row}.vue-form-wizard.vertical .wizard-card-footer{padding-top:30px}
</style>
