import React, { Component } from 'react'
import { hashHistory } from 'react-router'
import { Form, Icon, Input, Button, Checkbox, Radio, Row, Col,message } from 'antd'
const FormItem = Form.Item
const { TextArea } = Input
// const RadioButton = Radio.Button
const RadioGroup = Radio.Group
import UploadFile from '../../../common/upload/UploadFile'
import Fetch from 'utils/fetch'

class UploadForm extends Component{
  constructor(props){
    super(props)
    this.state={
      radioValue:'open',
      stopComment:false,
      allowDownload:false,
      autoUpSubmit:false,
      submitBtn:true,
      // fileKey:'',
      // isAuto:false,
      userId:'',
      upOpusId:''

    }
    console.log(this.props,'sjfjs');
  }
    handleSubmit = (e) => {
     e.preventDefault()
     this.props.form.validateFields((err, values) => {
       if (!err) {
         // console.log(this.state.userId);
         // console.log('wo hahha ');

         console.log('Received values of form: ', values)
         let formData = new FormData()
         // console.log(this.state.upOpusId,'id');
         formData.append('id',this.state.upOpusId)
         // formData.append('film_id',this.state.upOpusId)
         formData.append('name',values.title)
         if(values.synopsis !== undefined){
           formData.append('des',values.synopsis)
         }
         if(values.radios==='open'){
           formData.append('is_priviate',0)
         }else if(values.radios==='private'){
           formData.append('is_priviate',1)
         }

         if(values.stopComment === true){
           formData.append('is_reply',0)
         }
         if(values.allowDownload === true){
           formData.append('is_download',1)
         }
         if(values.passWordCome !== undefined){
           formData.append('password',values.passWordCome)
         }

         formData.append('address',this.props.fileKey)
         formData.append('size',this.props.fileSize)
         // console.log(this.props.fileKey,'dizhi');
         // console.log(this.props.fileSize,'size');
         Fetch.post({
           uri:'/api/test/doup',
           callback:(res)=>{
             console.log(res);
             if(res.message && res.message==="success"){
               message.success('上传作品成功')
               hashHistory.push('/opus')
             }
           },
           formData:formData
         })
       }
     })
   }
   // handleChangeSubmitDis=()=>{
   //   this.setState({
   //     submitBtn:false
   //   })
   // }
   test=(e)=>{
     const test = e.target.value
     // console.log(e.target.value);
     console.log(test.length);
   }
  render(){
     const { getFieldDecorator, getFieldValue } = this.props.form
     const formItemLayout = {
       labelCol: { span: 4 },
       wrapperCol:{ span: 19 },
     }
    return(
      <Form onSubmit={this.handleSubmit} className="login-form">
          <FormItem
            {...formItemLayout}
            label='标题'
          >
            {getFieldDecorator('title', {
              rules: [{required:true,max:40, min:5, message: '请输入大于五个字符小于四十的字符' }],
            })(
              <Input placeholder="请输入至少五个字符" onKeyDown={this.test}/>
            )}
          </FormItem>
          <FormItem
            {...formItemLayout}
            label='简介'
          >
            {getFieldDecorator('synopsis', {
              rules: [{max:200,min:5,message: '请输入大于五个字符小于二百的字符' }],
            })(
              <TextArea rows={4} placeholder="请输入至少五个字符" autosize={{minRows: 2, maxRows: 6 }}/>
            )}
          </FormItem>
          <FormItem
            {...formItemLayout}
            label='隐私'
          >
            <div>
              {getFieldDecorator('radios',{
                // valuePropName: 'checked',
                initialValue: 'open',
              })(
                <RadioGroup
                  value={this.state.radioValue} >
                  <Radio value="open">公开</Radio>
                  <Radio value='private'>私有</Radio>
                  <Radio value='password'>密码访问</Radio>
                </RadioGroup>
              )}
              <Row style={{display:getFieldValue('radios')==='open'? 'block' : 'none'}}>
                <Col span={10}>
                  {getFieldDecorator('stopComment',{
                    valuePropName: 'checked',
                    initialValue: false,
                  })(
                    <Checkbox>禁止评论和评分</Checkbox>
                  )}
                </Col>
                <Col span={8}>
                  {getFieldDecorator('allowDownload',{
                    valuePropName: 'checked',
                    initialValue: false,
                  })(
                  <Checkbox>允许下载</Checkbox>
                  )}
                </Col>
              </Row>
              <div style={{margin:'0 15px 0 0',display:getFieldValue('radios')==='password'? 'block' : 'none'}}>
                {getFieldDecorator('passWordCome')(
                  <Input placeholder="请输入密码" />
                )}
              </div>
            </div>
          </FormItem>

          <Button type="primary" htmlType="submit" className="login-form-button" disabled={this.state.submitBtn}>
            提交上传
          </Button>
        </Form>
      )
    }
    componentDidMount(){
      // console.log(this.props,'did');

      Fetch.post({
        uri:'/api/test/up',
        callback:(res)=>{
          // console.log(res);
          this.setState({
            userId:res.user_id,
            upOpusId:res.id
          })
          if(this.props.getUserId){
            this.props.getUserId(this.state.userId)
          }
          if(this.props.getFileId){
            this.props.getFileId(this.state.upOpusId)
          }
        }
      })
    }
    componentWillReceiveProps(nextProps){
      // console.log(nextProps);
      //是否自动上传
      if(this.props.isAuto !== nextProps.isAuto){
        this.props.isAuto=nextProps.isAuto
      }
      //获取文件的大小
      if(this.props.fileSize !== nextProps.fileSize){
        // console.log();
        this.props.fileSize = nextProps.fileSize
      }
      //获取上传文件的key
      if(this.props.fileKey !== nextProps.fileKey){
        if(nextProps.fileKey !== ''){
          this.props.fileKey=nextProps.fileKey
          this.setState({
            submitBtn:false
          })
          if(this.props.isAuto===true){
            this.handleSubmit(event)
          }
        }else{
          this.props.fileKey=nextProps.fileKey
          this.setState({
            submitBtn:true
          })
        }
      }
    }
  }

const UploadOpusForm = Form.create()(UploadForm)

export default UploadOpusForm
