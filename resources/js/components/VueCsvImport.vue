
<template>
      <div id="app">
        <div class="form">
          <form-wizard
              ref="wizard"
              color="#8191BD"
              error-color="#0080ff"
              @on-error="handleErrorMessage"
              @on-complete="onComplete"
              :hide-buttons="true">
              <h3 slot="title">Upload the CSV File below</h3>

            <!--This is the first  tab, before it changes to the next tab,it calls a function to upload the file, upload_file  -->
            <tab-content title="Upload File" icon="ti-file"  :before-change="upload_file">
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
                          <button   type="submit" :disabled="disabledNextButton" :class="buttonClass" @click.prevent="load" style="float:right;"> <!-- style="display:none;" visibility:hidden;   ref="load_file" -->
                               Upload File
                          </button>
                      </slot>
                  </div>
              </div>
            </tab-content>

            <!--This is the second tab before it changes to the next tab,it calls a function to match the columns match_columns -->
            <tab-content title="Match Columns" icon="ti-settings" :before-change="match_columns">
              <div class="vue-csv-uploader-part-two">
                <div v-if="errorMsg">
                  <span class="error">{{errorMsg}}</span>
                </div>
                <div v-else-if="errors">
                  <p style="color:green;">{{ errors[0] }}</p>
                </div>
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
                        <button type="submit" :disabled="disabledNextButton" :class="buttonClass"  @click.prevent="laststep"><!-- style="display:none;"visibility:hidden;   ref="columns" -->
                              Match Columns
                          </button>
                      </table>
                </div>
              </div>
            </tab-content>

            <!--This is the third and final tab -->
            <tab-content title="Finish" icon="ti-check">
              <div>
                <div v-if="errorMsg">
                  <span class="error">{{errorMsg}}</span>
                </div>
                <div v-else="errors">
                  <p style="color:green;">{{ errors[0] }}</p>
                </div>
                <div v-if="with_errors">
                  <p><h3 style="color:green;">Data successfully loaded!</h3></p>
                </div>
              </div>
              <div class='row' style="align:centre;">
                <button type="button"  :class="buttonClass" @click.prevent="redirect_to_upload"> <!-- :class="buttonClass"-->
                    Upload Again
                </button>
                &#160;&#160;&#160;
                <button type="button"  :class="buttonClass" @click.prevent="backhome"> <!-- :class="buttonClass"-->
                    Back Home
                  </button>
              </div>
            </tab-content>

          </form-wizard>
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
            title: {
            type: String,
            default: ''
            },
            url: {
                type: String
            },
            mapFields: {
                required: false, //true
                default:['Full Name','Loan Amount','Contract No','Loan Taken Date','Loan Due Date','Account No'] //,'Branch Title','Employee Email','Employee Name','NOK Name','NOK phone'
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
            must :[],
            loading: true,
            errorMsg: null,
            fieldsToMap: [],
            map: {},
            hasHeaders: true,
            csv: null,
            sample: null,
            isValidFileMimeType: false,
            fileSelected: false,
            data:null,
            options:[],
            isUsed: {},
            errors:[],
            with_errors:false

        }),
        created() {

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
          handleErrorMessage(errorMsg){
                this.errorMsg = errorMsg
          },
          upload_file(){
            return new Promise((resolve, reject) => {
              if(this.$refs.load_file.click()){
                this.$refs.load_file.click()
                resolve(true)
              }else{
                reject(false)
              }
            })
          },
          match_columns(){
            return new Promise((resolve, reject) => {
                if(this.$refs.columns.click()){
                  this.$refs.columns.click()
                  //console.log("Just matched!")
                  resolve(true)
                }else{
                  reject(false)
                  //console.log("not matched!")
                }
              })
          },
          submit() {
            const _this = this;
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
          laststep(){
            this.$refs.wizard.changeTab(1,2)
            const _this = this;
            console.log('The number of items in my CSV is ' + this.form.csv.length);
            //console.log(this.form.csv)
            axios.post('http://127.0.0.1:8000/api/csv', this.form.csv)
            .then((response) => {
                this.data = response
                console.log("Data sent to http://127.0.0.1:8000/api/csv ")
                this.data = this.form.csv
                console.log(this.data)
                //console.log(response.data)
                console.log("This was successfully done")
            }).catch(err => { console.log(err)});
            if(this.errors.length>0){
              console.log("iko na errors")
            }else{
              console.log("haina errors")
            }
          },
          load() {
            this.$refs.wizard.changeTab(0,1)
            const _this = this;
            this.readFile((output) => {
                _this.sample = _.get(Papa.parse(output, { preview: 2, skipEmptyLines: true }), "data");
                _this.csv = _.get(Papa.parse(output, { skipEmptyLines: true }), "data");

                for(let i = 0; i < _this.sample[0].length; i++){
                   this.options.push(_this.sample[0][i]);
                }
            });
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

            //pick the file selected
            let file = this.$refs.csv.files[0];

            //check the type of the file and assign to a variable mimetype...
            const mimeType = file.type === "" ? mimeTypes.lookup(file.name) : file.type;

            if (file) {
               this.fileSelected = true;
               this.isValidFileMimeType = this.validation ? this.validateMimeType(mimeType) : true;
            }
            else{
                this.isValidFileMimeType = !this.validation;
                this.fileSelected = false;
            }
          },
          validateMimeType(type) {
            return this.fileMimeTypes.indexOf(type) > -1;
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
            this.isUsed = {
            ...this.isUsed,[option]: true
            };
          },
          backhome() {
              location.replace('http://127.0.0.1:8000');
          },
          redirect_to_upload() { // this method is called on button click
            //console.log("Upload Again")
            location.replace('http://127.0.0.1:8000');
          }
        },
        watch: {
            map: {
                deep: true,
                handler: function (newVal) {
                    if (!this.url) {

                        this.must.push(this.mapFields[0])
                        this.must.push(this.mapFields[1])
                        this.must = [...new Set(this.must)];
                        //console.log(this.must)

                        /*let hasAllKeys = Array.isArray(this.mapFields) ? _.every(this.mapFields, function (item) {
                            return newVal.hasOwnProperty(item);
                        }) : _.every(this.mapFields, function (item, key) {
                            return newVal.hasOwnProperty(key);
                        }); */

                        let hasAllKeys = Array.isArray(this.must) ? _.every(this.must, function (item) {
                            return newVal.hasOwnProperty(item);
                         }) : _.every(this.this.must, function (item, key) {
                            return newVal.hasOwnProperty(key);
                          });

                        if (hasAllKeys) {
                            this.errors = [];
                            this.submit();
                          }else{
                          this.errors.push("Please make sure you match Full Name and Loan Amount.");
                          this.errors.push("Please make sure you match Full Name");
                          this.with_errors = true
                          //console.log("Fill all the columns first")
                        }
                        /*

                        if (hasAllKeys) {
                            this.errors = [];
                            this.submit();
                          }else{
                          this.errors.push("Full Name and Loan Amount Fields must be Matched.");
                          console.log("Fill all the columns first")
                        }

                        try {

                          this.errors = [];
                          this.submit();
                        } catch(err) {
                            console.log('Ohh no:', err.message);
                        } */


                        //this.submit();
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
            with_errors:false,
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
</style>
