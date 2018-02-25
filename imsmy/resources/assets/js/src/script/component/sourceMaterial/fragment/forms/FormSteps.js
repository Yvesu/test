import React, { Component } from 'react'

import { Form, Input, Button, Select, TimePicker, Checkbox,Row, Col } from 'antd'
const FormItem = Form.Item
const Option = Select.Option

import moment from 'moment'
import FetchPost from 'utils/fetch'
import AddTags from '../../common/add/AddTags'

import Uploads from '../../../common/uploads/Uploads'
import UploadsImg from '../../../common/uploads/UploadsImg'
import UploadsFile from '../../../common/uploads/UploadsFile'
import { uploadButtonImg, uploadButtonVideo, uploadButtonZIP } from '../../../common/uploads/UploadBtn'
import FileType from '../../../common/uploads/UploadFileType'

class FormStep extends React.Component {
  constructor(props){
    super(props)
    this.state={
      disabled:true,
      userId:'',
      eventId:"",
      selectRatio:[],
      data:[],
      cost:[],
      type:[],
      channel:[],
      keyword:[],
      coverUrl:"",
      videoUrl:"",
      zipUrl:"",
      fileSize:'',
      shotNumber:[],
      country:[], //国家
      province:[],
      city:[],
      county:[],
      provinceDisabled:true,
      cityDisabled:true,
      countyDisabled:true,
      countryValue:"请选择国家",
      provinceValue:"请选择省份", //省份
      cityValue:"请选择城市", //城市
      countyValue:"请选择区(县)", //区县
      streetValue:'',//详细地址
    }

  }
  handleSubmit = (e) => {
    e.preventDefault()
    console.log(this.props.form.getFieldsValue());
    this.props.form.validateFields((err, values) => {
      if (!err) {
        console.log('Received values of form:', values)
      }
    })
  }

//改变checkbox的状态
  handleCheckChange = (value) => {
    this.setState({
      disabled:false
    })
    if(value === 0){
        this.setState({
          disabled:true
        })
    }

  }
  //添加分类
  AddType=()=>{
    FetchPost.post({
      uri:'/api/admins/fodder/issue/fragment/addtype',
      callback:(res)=>{
        this.setState({
          type:res.data
        })
      }
    })
  }
  //添加画面比例
  AddAspectRadio=()=>{
    FetchPost.post({
      uri:'/api/admins/fodder/issue/fragment/addaspectradio',
      callback:(res)=>{
        this.setState({
          selectRatio:res.data
        })
      }
    })
  }
  //添加下载资费
  AddIntergal=()=>{
    FetchPost.post({
      uri:'/api/admins/fodder/issue/fragment/addintergal',
      callback:(res)=>{
        this.setState({
          cost:res.data
        })
      }
    })
  }
  //添加镜头数量
  AddShotNumber=()=>{
    var arr = []
    for(var i=1;i<=20;i++){
      arr.push(i)
    }
    this.setState({
      shotNumber:arr
    })

  }
  //将获取到的数组和关键字转化成为数组
  resolveObjEvent=(data,type)=>{
    var obj = data
    var props = []
    for(var i in obj){
        props.push(obj[i])
    }
    if(type === "channel"){
      this.setState({
        channel:props
      })
    }else{
      this.setState({
        keyword:props
      })
    }
  }
//改变关键字的值
  handleChangeTags=(value)=>{
    this.setState({
      keyword:value
    })
  }
  //改变上传封面的值
  handleChangeCover=(value)=>{
    this.setState({
      coverUrl:value
    })
  }
  //改变上传视频的值
  handleChangeVideoUrl=(value)=>{
    this.setState({
      videoUrl:value
    })
  }
  //改变上传文件的值
  handleChangeZipUrl=(value)=>{
    this.setState({
      zipUrl:value
    })
  }
  //改变上传文件的大小
  handleFileSize=(value)=>{
    this.setState({
      fileSize:value
    })
  }
  //接收后台数据 改变默认添加的网址
  handleChangeUrl=(dataUrl,type)=>{
    var obj = dataUrl
    var objType = type
    const newObj = obj.replace(/http:(\S*).com[^\s]/,'')
    if(newObj === ''){
      if(objType === 'cover'){
        this.setState({
          coverUrl:""
        })
      }else if(objType === 'video'){
        this.setState({
          videoUrl:''
        })
      }else{
        this.setState({
          zipUrl:""
        })
      }
    }else{
      if(objType === 'cover'){
        this.setState({
          coverUrl:newObj
        })
      }else if(objType === 'video'){
        this.setState({
          videoUrl:newObj
        })

      }else{
        this.setState({
          zipUrl:newObj
        })
      }
    }
  }
  //联动开始  - 改变国家的值
  //获取国家
  handleCountry=()=>{
    FetchPost.post({
      uri:'/api/admins/fodder/issue/fragment/addresscountry',
      callback:(res)=>{
        this.setState({
          country:res.data
        })
      }
    })
  }
  //改变国家  获取省份或者城市
  handleChangeCountry=(value)=>{
    this.setState({
      provinceDisabled:true,
      cityDisabled:true,
      countyDisabled:true,
    })
    const GetCountryName = this.state.country
    for(let i=0;i<GetCountryName.length;i++){
      if(GetCountryName[i].id === value){
        this.setState({
          countryValue:GetCountryName[i].Name
        })
      }
    }

    this.props.form.setFieldsValue({
      addressProvince:'请选择省份',
      addressCity:'请选择城市',
      addressCounty:"请选择区(县)"
    })

      let formData=new FormData()
      formData.append('id',value)
      FetchPost.post({
        uri:'/api/admins/fodder/issue/fragment/addressprovince',
        callback:(res)=>{
          if(res.data.length>0){
            this.setState({
              province:res.data,
              provinceDisabled:false,
            })
          }else{
            this.setState({
              province:[],
              provinceDisabled:true,
            })
            FetchPost.post({
              uri:'/api/admins/fodder/issue/fragment/addressstate',
              callback:(res)=>{
                if(res.data.length>0){
                  this.setState({
                    city:res.data,
                    cityDisabled:false,
                  })
                }else{
                  this.setState({
                    city:[],
                    cityDisabled:true,
                  })
                }
              },
              formData:formData
            })
          }
        },
        formData:formData
      })
  }
  //改变省份 获取城市
  handleChangeProvince=(value)=>{
    if(this.state.provinceDisabled===false){
      this.setState({
        cityDisabled:true,
        countyDisabled:true,
      })
      const GetProvinceName = this.state.province
      for(let i=0;i<GetProvinceName.length;i++){
        if(GetProvinceName[i].id === value){
          this.setState({
            provinceValue:GetProvinceName[i].Name
          })
        }
      }
      this.props.form.setFieldsValue({
        addressCity:'请选择城市',
        addressCounty:"请选择区(县)"
      })

      let formData= new FormData()
      formData.append('id',value)
      FetchPost.post({
        uri:'/api/admins/fodder/issue/fragment/addresscity',
        callback:(res)=>{
          if(res.data.length>0){
            this.setState({
              city:res.data,
              cityDisabled:false,
            })
          }else{
            this.setState({
              city:[],
              cityDisabled:true,
            })
          }
        },
        formData:formData
      })
    }

  }
  //改变城市 获取区/县
  handleChangeCity=(value)=>{
    if(this.state.cityDisabled === false){
      this.setState({
        countyDisabled:true,
      })
      const GetCitySet = this.state.city
      const GetArraySolo = []
      for(let i=0;i<GetCitySet.length;i++){
         if(GetCitySet[i].id === value){
           GetArraySolo.push(GetCitySet[i])
           this.setState({
             cityValue:GetCitySet[i].Name
           })
         }
      }
      this.props.form.setFieldsValue({
        addressCounty:"请选择区(县)"
      })
      if(GetArraySolo[0].Pid){
        const Code = GetArraySolo[0].Code
        const Pid = GetArraySolo[0].Pid
        const Tid = GetArraySolo[0].Tid
        let formData = new FormData()
        formData.append('Code',Code)
        formData.append('Pid',Pid)
        formData.append('Tid',Tid)
        FetchPost.post({
          uri:'/api/admins/fodder/issue/fragment/addresscounty',
          callback:(res)=>{
            if(res.data.length>0){
              this.setState({
                county:res.data,
                countyDisabled:false,
              })
            }else{
              this.setState({
                county:[],
                countyDisabled:true,
              })
            }
          },
          formData:formData
        })
      }
    }
  }
  //改变区县
  handleChangeCounty=(value)=>{
    if(this.state.countyDisabled===false){
      const GetCountyName = this.state.county
      for(let i=0;i<GetCountyName.length;i++){
        if(GetCountyName[i].id === value){
          this.setState({
            countyValue:GetCountyName[i].Name
          })
        }
      }
    }
  }
  //失去焦点开始 - 国家
  CountryBlur=()=>{
    this.props.form.setFieldsValue({
      addressCountry:this.state.countryValue,
    })
  }
  //省份失去焦点
  ProvinceBlur=()=>{
    this.props.form.setFieldsValue({
      addressProvince:this.state.provinceValue,
    })
  }
  //城市失去焦点
  CityBlur=()=>{
    this.props.form.setFieldsValue({
      addressCity:this.state.cityValue,
    })
  }
  //区县失去焦点
  CountyBlur=()=>{
    this.props.form.setFieldsValue({
      addressCounty:this.state.countyValue
    })
  }

  // 联动结束 - 失去焦点结束

  //向后台请求数据
  handleFetchPost(){
    FetchPost.post({
      uri:'/api/admins/fodder/issue/fragment/base',
      callback:(res)=>{
        console.log(res,'pianduan');
        this.setState({
          data: res.data[0],
          userId:res.data[0].user_id,
          eventId:res.data[0].id,
          coverUrl:res.data[0].cover,
          videoUrl:res.data[0].net_address,
          zipUrl:res.data[0].zip_address,
          fileSize:res.data[0].size,

        })
        if(res.data[0].channel){
          this.resolveObjEvent(res.data[0].channel,"channel")
        }
        if(res.data[0].keyword){
          this.resolveObjEvent(res.data[0].keyword)
        }
        if(res.data[0].cover){
          this.handleChangeUrl(res.data[0].cover,'cover')
        }
        if(res.data[0].net_address){
          this.handleChangeUrl(res.data[0].net_address,'video')
        }
        if(res.data[0].zip_address){
          this.handleChangeUrl(res.data[0].zip_address)
        }
      }
    })
  }


  render() {
    const { getFieldDecorator } = this.props.form
    const formItemLayout = {
      labelCol: { span: 3 },
      wrapperCol: { span: 14 },
    }

    return (
        <Form onSubmit={this.handleSubmit.bind(this)}>
          <FormItem
            {...formItemLayout}
            label="片段分类"
          >
            {getFieldDecorator('channel', {
              initialValue:this.state.channel,
              rules: [
                { required: true, message: '最多选择三个分类！', type: 'array',max:3 },
              ],
            })(
              <Select mode="multiple" placeholder={"请选择分类"} onFocus={this.AddType}>
                {
                  this.state.type.map((value,index)=>{
                    return(
                      <Option value={value.type}>{value.type}</Option>
                    )
                  })
                }
              </Select>
            )}
          </FormItem>
          <FormItem  {...formItemLayout} label="片段描述">
            {getFieldDecorator('name', {
              initialValue:this.state.data.name,
              rules: [{ required: true, max: 200, message: '请输入小于200字符的内容！' }] })
            (<Input type="textarea"
              placeholder={'描述该片段内容，少于200字符'}
              style={{overflow:'hidden'}} />)
            }
          </FormItem>
          <FormItem
            {...formItemLayout}
            wrapperCol={{span:5}}
            label="画面比例"
          >
          {getFieldDecorator('aspect_radio', {
            initialValue:this.state.data.aspect_radio,
            rules: [
              { required: true, message: '请选择画面比例！' },
            ],
          })(
            <Select placeholder={"请选择画面比例"} onFocus={this.AddAspectRadio}>
              {
                this.state.selectRatio.map((value,index)=>{
                  return (
                    <Option value={value.value}>{value.value}</Option>
                  )
                })
              }

            </Select>
          )}

        </FormItem>
        <FormItem
          {...formItemLayout}
          label="时长"
        >
          {getFieldDecorator('duration',{
            initialValue:moment(this.state.data.duration,'mm:ss'),
            rules: [{ required: true, type: 'object', message: '请选择时长!' }]
          })(
            <TimePicker placeholder={"请选择时长"}
              defaultOpenValue={moment('00:00', 'mm:ss')} format={'mm:ss'}
              value={this.state.data.duration}
            />
          )}
        </FormItem>
        <FormItem
          {...formItemLayout}
          wrapperCol={{span:4}}
          label="镜头数量"
        >
        {getFieldDecorator('shotNumber', {
          initialValue:this.state.shotNumber,
          rules: [
            { required: true, message: '请选择镜头数量' },
          ],
        })(
          <Select placeholder={"请选择镜头数量"} onFocus={this.AddShotNumber}>
            {
              this.state.shotNumber.map((value,index)=>{
                return (
                  <Option value={value}>{value}</Option>
                )
              })
            }

          </Select>
        )}

      </FormItem>
        <FormItem
          {...formItemLayout}

          label="下载资费"
        >
          <p style={{float:'left',width:"40%"}}>
            {getFieldDecorator('intergral', {
              initialValue:this.state.data.intergral,
            })(
              <Select placeholder="免费" onFocus={this.AddIntergal} onChange={this.handleCheckChange}>
                {
                  this.state.cost.map((value,index)=>{
                    return(
                      <Option value={value.intergal}>{value.intergal}</Option>
                    )
                  })
                }
              </Select>
            )}
          </p>
          {/* <p>vipefree改为1 默认值是1 。后边还得重新修改</p> */}
          <p style={{float:"right",width:"50%"}}>
            {getFieldDecorator('vipfree', {
              valuePropName: 'checked',
              initialValue: `${this.state.data.vipfree === "1" ? true : false}`,
            })(
              <Checkbox disabled={this.state.disabled}>
                  VIP是否免费
                </Checkbox>
            )}
          </p>
        </FormItem>
        <Row gutter={8}>
          <Col span={7} offset={1}>
            <FormItem
              labelCol={{ span:7 }}
              wrapperCol={{ span: 17 }}
              label="拍摄地址"
            >
              {getFieldDecorator('addressCountry', {
                initialValue: this.state.countryValue ,
              })(
                <Select defaultValue="请选择国家" value={this.state.countryValue}
                  onChange={this.handleChangeCountry} onFocus={this.handleCountry}
                  onBlur={this.CountryBlur} >
                  {
                    this.state.country.map((value,index)=>{
                      return(
                        <Option value={value.id}>{value.Name}</Option>
                      )
                    })
                  }
                </Select>
              )}
            </FormItem>
          </Col>
          <Col span={5} >
            <FormItem
              {...formItemLayout}
              wrapperCol={{ span: 24 }}
            >
              {getFieldDecorator('addressProvince', {
                initialValue: this.state.provinceValue ,
              })(
                <Select defaultValue='请选择省份' disabled={this.state.provinceDisabled}
                    value={this.state.provinceValue} onChange={this.handleChangeProvince}
                    onBlur={this.ProvinceBlur} >
                  {
                    this.state.province.map((value,index)=>{
                      return(
                        <Option value={value.id} key={index}>{value.Name}</Option>
                      )
                    })
                  }
                </Select>
              )}
            </FormItem>
          </Col>
          <Col span={5} >
            <FormItem
              {...formItemLayout}
              wrapperCol={{ span: 24 }}
            >
              {getFieldDecorator('addressCity', {
                initialValue: this.state.cityValue,
              })(
                <Select defaultValue="请选择城市" value={this.state.cityValue}
                     onChange={this.handleChangeCity} disabled={this.state.cityDisabled}
                     onBlur={this.CityBlur} >
                    {
                      this.state.city.map((value,index)=>{
                        return(
                          <Option value={value.id} key={index}>{value.Name}</Option>
                        )
                      })
                    }
                </Select>
              )}
            </FormItem>
          </Col>
          <Col span={5} >
            <FormItem
              {...formItemLayout}
              wrapperCol={{ span: 24 }}
            >
              {getFieldDecorator('addressCounty', {
                initialValue: this.state.countyValue,
              })(
                <Select defaultValue="请选择区(县)" value={this.state.countyValue}
                    onChange={this.handleChangeCounty} disabled={this.state.countyDisabled}
                    onBlur={this.CountyBlur} >
                    {
                      this.state.county.map((value,index)=>{
                        return(
                          <Option value={value.id} key={index}>{value.Name}</Option>
                        )
                      })
                    }
                </Select>
              )}
            </FormItem>
          </Col>
        </Row>
      <FormItem
        labelCol={{span:3}}
        wrapperCol={{ span: 15 }}
        label=" "
        colon={false}
      >
        {getFieldDecorator('addressStreet',{
            initialValue:this.state.streetValue,
        })(
          <Input placeholder='详细信息' />
        )}

      </FormItem>
      <FormItem
        {...formItemLayout}
        label="添加标签"
      >
        {getFieldDecorator('keyword',{
            initialValue:this.state.keyword,
        })(
          <AddTags onChange={this.handleChangeTags} newKeyWords={this.state.keyword} />
        )}

      </FormItem>
      <Row className="upload_fragment_box">
        <Col span={6}>
          <FormItem
            wrapperCol={{span: 24}}
            label='封面:'
            className="upload_fragment_formItem_title"
          >
            {getFieldDecorator('cover', {
              initialValue:this.state.coverUrl,
              rules: [ {message: '请上传封面' } ],
            })(
              <UploadsImg uploadButton={uploadButtonImg} beforeUpload={FileType.beforeUploadImg}
                name={`fragment/cover/admins/${this.state.userId}/${this.state.eventId}/`}
                onChange={this.handleChangeCover}
                showFile={this.state.coverUrl}
              />
            )}
            <p style={{fontSize:12, color:"#c3c3c3"}}>*支持扩展名：.png .gif .jpg</p>
          </FormItem>
        </Col>
        <Col span={6} offset={3}>
          <FormItem
            wrapperCol={{span: 24}}
            label="演示:"
            className="upload_fragment_formItem_title"
          >
            {getFieldDecorator('videoDemo', {
              initialValue:this.state.videoUrl,
              rules: [{ message: '请上传视频！' } ],
            })(
              <Uploads uploadButton={uploadButtonVideo} beforeUpload={FileType.beforeUploadVideo}
                name={`fragment/video/admins/${this.state.userId}/${this.state.eventId}/`}
                onChange={this.handleChangeVideoUrl}
                showFile={this.state.videoUrl}
              />
            )}
            <p style={{fontSize:12, color:"#c3c3c3"}}>*支持扩展名：.mov . mp4</p>
          </FormItem>
        </Col>
        <Col span={6} offset={3}>
          <FormItem
            wrapperCol={{span: 24}}
            label="上传片段:"
            className="upload_fragment_formItem_title"
          >
            {getFieldDecorator('fileZip', {
              initialValue:this.state.zipUrl,
              rules: [{ message: '请选择片段！' }],
            })(
              <UploadsFile uploadButton={uploadButtonZIP} beforeUpload={FileType.beforeUploadZIP}
                name={`fragment/zip/admins/${this.state.userId}/${this.state.eventId}/`}
                onChange={this.handleChangeZipUrl}
                fileSize={this.handleFileSize}
                showFile={this.state.zipUrl}
              />
            )}
            <p style={{fontSize:12, color:"#c3c3c3"}}>*支持扩展名：.zip</p>
          </FormItem>
        </Col>
      </Row>
      <FormItem
        {...formItemLayout}
      >
        {getFieldDecorator('fragmentId',{initialValue:this.state.eventId})(
          <div></div>
        )}
      </FormItem>
      <FormItem
        {...formItemLayout}
      >
        {getFieldDecorator('fileSize',{initialValue:this.state.fileSize})(
          <div></div>
        )}
      </FormItem>
          {/* <Button htmlType="submit">one</Button>
          {/* <Button onClick={this.handleChangeValues}>1211</Button> */}

      </Form>

    )
  }
  componentDidMount(){
    this.handleFetchPost()
  }
  // componentWillReceiveProps(nextPorps){
  //   // console.log(nextPorps);
  //   this.handleFetchPost()
  //   // this.handleChangeFileList(this.props.showFile)
  // }

}

const FormSteps = Form.create()(FormStep)
export default FormSteps
