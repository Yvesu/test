import React, { Component } from 'react'

import { Form, Input, Button, Select, TimePicker, Checkbox,Row, Col,InputNumber } from 'antd'
const FormItem = Form.Item
const Option = Select.Option
import moment from 'moment'

import Fetch from 'utils/fetch'
import AddTags from '../../../common/add/AddTags'

import Uploads from '../../../../common/uploads/Uploads'
import UploadsImg from '../../../../common/uploads/UploadsImg'
import UploadsFile from '../../../../common/uploads/UploadsFile'
import { ButtonLibraryCover, ButtonLibraryVideo, ButtonLibraryFile } from '../../../../common/uploads/UploadBtn'
import FileType from '../../../../common/uploads/UploadFileType'

class LibraryUploadFirst extends React.Component {
  constructor(props){
    super(props)
    this.state={
      disabled:true,
      userId:'',
      eventId:"",
      data:[],
      cost:[],
      type:[],
      keyword:[],
      coverUrl:"",
      videoUrl:"",
      zipUrl:"",
      fileSize:'',


    }

  }
  handleSubmit = (e) => {
    e.preventDefault()
    // console.log(this.props.form.getFieldsValue());
    this.props.form.validateFields((err,values) => {
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
      this.state.data.vipfree = 1
      this.setState({
        disabled:true
      })
    }
  }
  //添加分类
  AddType=()=>{
    Fetch.post({
      uri:'/api/admins/fodder/mixresource/common/type',
      callback:(res)=>{
        console.log(res);
        this.setState({
          type:res.data
        })
      }
    })
  }

  //添加下载资费
  AddIntergal=()=>{
    Fetch.post({
      uri:'/api/admins/fodder/mixresource/common/downloadcost',
      callback:(res)=>{
        this.setState({
          cost:res.data
        })
      }
    })
  }
  // handleChangeTime=(time,timeString)=>{
  //   console.log(timeString,'ss');
  //   this.setState({
  //     duration:timeString
  //   })
  //   console.log(this.state.duration);
  // }

  //将获取到的数组和关键字转化成为数组
  resolveObjEvent=(data)=>{
    var obj = data
    var props = []
    for(var i in obj){
        props.push(obj[i])
    }
    this.setState({
      keyword:props
    })

  }
//改变关键字的值
  handleChangeTags=(value)=>{
    this.setState({
      keyword:value
    })
  }
//  改变上传封面的值
  handleChangeCover=(value)=>{
    this.setState({
      coverUrl:value
    })
  }
  // 改变上传视频的值
  handleChangeVideoUrl=(value)=>{
    this.setState({
      videoUrl:value
    })
  }
  // 改变上传文件的值
  handleChangeFileVideoUrl=(value)=>{
    this.setState({
      zipUrl:value
    })
  }
  // 改变上传文件的大小
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


  //向后台请求数据
  handleFetchPostData=()=>{
    Fetch.post({
      uri:'/api/admins/fodder/mixresource/issue',
      callback:(res)=>{
        // console.log(res.data,'da');
        this.setState({
          data: res.data,
          userId:res.data.user_id,
          eventId:res.data.id,
          coverUrl:res.data.cover,
          videoUrl:res.data.preview_address,
          zipUrl:res.data.address,
          fileSize:res.data.size,
        })
        if(this.props.isEmptyForm){
          this.props.isEmptyForm=false
        }
        if(res.data.folder_id !== null){
          this.AddType()
        }
        // if(res.data[0].channel){
        //   this.resolveObjEvent(res.data[0].channel,"channel")
        // }

        if(res.data.keyword && res.data.keyword.keyword0 !== ''){
          this.resolveObjEvent(res.data.keyword)
        }
        if(res.data.cover){
          this.handleChangeUrl(res.data.cover,'cover')
        }
        if(res.data.preview_address){
          this.handleChangeUrl(res.data.preview_address,'video')
        }
        if(res.data.address){
          this.handleChangeUrl(res.data.address)
        }
      }
    })
  }


  render() {
    const { getFieldDecorator } = this.props.form
    const formItemLayout = {
      labelCol: { span: 3 },
      wrapperCol: { span: 8 },
    }

    return (
        <Form onSubmit={this.handleSubmit.bind(this)}>
        <FormItem
            {...formItemLayout}
            wrapperCol={{span:8}}
            label="特效分类："
          >
            {getFieldDecorator('type', {
              initialValue:this.state.data.folder_id===null? undefined : this.state.data.folder_id,
              rules: [
                { required: true, message: '请选择分类！'},
              ],
            })(
              <Select placeholder={"请选择分类"} onFocus={this.AddType}>
                {
                  this.state.type.map((value,index)=>{
                    return(
                      <Option value={value.id}>{value.type}</Option>
                    )
                  })
                }
              </Select>
            )}
          </FormItem>
          <FormItem  {...formItemLayout} label="资源名称：">
            {getFieldDecorator('name', {
              initialValue:this.state.data.name,
              rules: [{ required: true, max: 20, message: '请输入小于20字符的内容！' }] })
            (<Input
              placeholder={'少于20字符'}
            />)
            }
          </FormItem>
          <Row>
            <Col span={8}>
              <FormItem
                labelCol={{span:9}}
                wrapperCol={{span:15}}
                label="下载资费"
              >
                {getFieldDecorator('intergral', {
                  initialValue:`${(this.state.data.integral === undefined || this.state.data.integral===0 || this.state.data.integral===null)? '免费' : this.state.data.integral}`,
                  rules:[{required: true,message:'请选择资费'}]
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
              </FormItem>
            </Col>
            <Col span={15} offset={1}>
              <FormItem >
                {getFieldDecorator('vipfree', {
                  valuePropName: 'checked',
                  initialValue: `${(this.state.data.vipfree === 1 || this.state.data.vipfree===undefined) ? true : false}`,

                })(
                  <Checkbox disabled={this.state.disabled}>
                      VIP是否免费
                    </Checkbox>
                )}
              </FormItem>
            </Col>
          </Row>

    {/* <p>vipefree改为1 默认值是1 。后边还得重新修改</p> */}
          <FormItem
            {...formItemLayout}
            label="时长"
            >
              {getFieldDecorator('duration',{
                initialValue:moment(this.state.data.duration, 'mm:ss'),
                rules: [{ required: true, type: 'object', message: '请选择时长!' }]
              })(
                <TimePicker placeholder={"请选择时长"}
                  // defaultValue={this.state.data.duration===null? "00:00" : this.state.data.duration}
                  defaultOpenValue={moment('00:00', 'mm:ss')} format={'mm:ss'}
                  value={this.state.data.duration}
                />
              )}
            </FormItem>
            <Row gutter={16}>
              <Col span={6}>
                <FormItem
                  // {...formItemLayout}
                  labelCol={{span:13}}
                  wrapperCol={{span:11}}
                  label='分辨率'
                >
                  {getFieldDecorator('widths', {
                    initialValue:this.state.data.distinguishability_x,
                    rules: [{ required: true, type: 'number', message: '请输入数字！' }] })
                  (<InputNumber placeholder={'宽'} min={0}
                  />)
                  }
                </FormItem>
              </Col>
              <Col span={1}>
                  <p style={{color:'#000',fontWeight:500,fontSize:16,paddingLeft:8,paddingTop:5}}>
                  {" * "}
                  </p>
              </Col>
              <Col span={4}>
                <FormItem>
                  {getFieldDecorator('heights', {
                    initialValue:this.state.data.distinguishability_y,
                    rules: [{ required: true, type: 'number', message: '请输入数字！' }] })
                  (<InputNumber placeholder={'高'} min={0}
                  />)
                  }
                </FormItem>
              </Col>
              <Col span={10}>
                <FormItem>
                  {getFieldDecorator('Alpha', {
                    valuePropName: 'checked',
                    initialValue: `${(this.state.data.isalpha === 1 || this.state.data.isalpha===undefined) ? true : false}`,
                  })(
                    <Checkbox> Alpha通道 </Checkbox>
                  )}
                </FormItem>
              </Col>
            </Row>
            <Row>
              <Col span={4} offset={1}>
                <FormItem>
                  {getFieldDecorator('cover', {
                    initialValue:this.state.coverUrl,
                    rules: [ {message: '请上传封面' } ],
                  })(
                    <UploadsImg uploadButton={ButtonLibraryCover} beforeUpload={FileType.beforeUploadLibraryCover}
                      name={`mix/library/cover/admins/${this.state.userId}/${this.state.eventId}/`}
                      onChange={this.handleChangeCover}
                      showFile={this.state.coverUrl}
                    />
                  )}
                  <p style={{fontSize:12, color:"#c3c3c3"}}>*支持扩展名：.gif .jpg</p>
                </FormItem>
              </Col>
              <Col span={4} offset={2}>
                <FormItem >
                  {getFieldDecorator('videoDemo', {
                    initialValue:this.state.videoUrl,
                    rules: [{ message: '请上传视频！' } ],
                  })(
                    <Uploads uploadButton={ButtonLibraryVideo} beforeUpload={FileType.beforeUploadLibraryVideo}
                      name={`mix/library/video/admins/${this.state.userId}/${this.state.eventId}/`}
                      onChange={this.handleChangeVideoUrl}
                      showFile={this.state.videoUrl}
                    />
                  )}
                  <p style={{fontSize:12, color:"#c3c3c3"}}>*支持扩展名：.mp4</p>
                </FormItem>
              </Col>
              <Col span={5} offset={2}>
                <FormItem>
                  {getFieldDecorator('fileZip', {
                    initialValue:this.state.zipUrl,
                    rules: [{ message: '请选择资源！' }],
                  })(
                    <Uploads uploadButton={ButtonLibraryFile} beforeUpload={FileType.beforeUploadVideo}
                      name={`mix/library/fileVideo/admins/${this.state.userId}/${this.state.eventId}/`}
                      onChange={this.handleChangeFileVideoUrl}
                      fileSize={this.handleFileSize}
                      showFile={this.state.zipUrl}
                    />
                  )}
                  <p style={{fontSize:12, color:"#c3c3c3"}}>* 支持扩展名：.mp4 .mov</p>
                </FormItem>
              </Col>
            </Row>

      <FormItem
        {...formItemLayout}
        label="标签"
      >
        {getFieldDecorator('keyword',{
            initialValue:this.state.keyword,
        })(
          <AddTags onChange={this.handleChangeTags} uri="/api/admins/fodder/mixresource/issue"
            newKeyWords={this.state.keyword}
        />
        )}

      </FormItem>

      <FormItem
        {...formItemLayout}
      >
        {getFieldDecorator('libraryId',{initialValue:this.state.eventId})(
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
          {/* <Button htmlType="submit">one</Button> */}
          {/* <Button onClick={this.handleChangeValues}>1211</Button> */}
      </Form>

    )
  }
  componentDidMount(){
    // console.log(this.props.isEmptyForm);
    this.handleFetchPostData()
  }
  componentWillReceiveProps(nextPorps){
    // console.log(nextPorps,'nextpropsLibrary');
    if(nextPorps.isEmptyForm !== false){
      this.handleFetchPostData()
    }
    // this.handleChangeFileList(this.props.showFile)
  }

}

const UploadFirst = Form.create()(LibraryUploadFirst)
export default UploadFirst
