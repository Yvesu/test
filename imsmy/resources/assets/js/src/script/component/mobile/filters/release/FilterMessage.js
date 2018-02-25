import React, { Component } from 'react'
import { Form,Row, Col, Input, Button, Select,Checkbox } from 'antd'
const FormItem = Form.Item
const Option = Select.Option
import AddTags from '../add/AddTags'
import { uploadButtonImg, uploadButtonFilter, uploadButtonVein } from '../../../common/uploads/UploadBtn'
import FileType from '../../../common/uploads/UploadFileType'
import UploadsImg from '../../../common/uploads/UploadsImg'
import UploadsFile from '../../../common/uploads/UploadsFile'
import Fetch from 'utils/fetch'

class FilterSteps extends React.Component {
  constructor(props){
    super(props)
    this.state={
      userId:"",
      filterId:'',
      disabled:true,
      veinSelect:true,
      addIntegral:[],
      textureBlend:[],
      addFiterType:[],
      coverUrl:'',
      hiColorUrl:'',
      veinUrl:'',
      addTags:[]
    }

  }
  handleSubmit = (e) => {
    e.preventDefault()
    this.props.form.validateFields((err, values) => {
      if (!err) {
        console.log('Received values of form:', values)
      }
    })
  }

  handleTagsChange = (tags) => {
    this.setState({
      addTags:tags
    })
  }
  handleFilterCoverUrl=(value)=>{
    this.setState({
      coverUrl:value
    })
  }
  handleFilterHiFile=(value)=>{
    this.setState({
      hiColorUrl:value
    })
  }
  // handleFilterVeinUrl=(value)=>{
  //   if(value != ''){
  //     this.setState({
  //       veinUrl:value,
  //       veinSelect:false
  //     })
  //   }else{
  //     this.setState({
  //       veinUrl:value,
  //       veinSelect:true
  //     })
  //   }
  // }

  handleCheckChange = (value) => {
    // console.log(value);
    this.setState({
      disabled:false
    })
    if(value === 0){
        this.setState({
          disabled:true
        })
    }

  }
  AddFilterType=()=>{
    Fetch.post({
      uri:'/api/admins/mobile/filter/addfiltertype',
      callback:(res)=>{
        // console.log(res);
        this.setState({
          addFiterType:res.data
        })
      }
    })
  }
  AddFilterIntegral=()=>{
    Fetch.post({
      uri:'/api/admins/mobile/filter/getintegral',
      callback:(res)=>{
        // console.log(res);
        this.setState({
          addIntegral:res.data
        })
      }
    })
  }
  AddTextureBlend=()=>{
    Fetch.post({
      uri:'/api/admins/mobile/filter/gettexturemixtype',
      callback:(res)=>{
        // console.log(res);
        this.setState({
          textureBlend:res.data
        })
      }
    })
  }

  render() {
    const { getFieldDecorator } = this.props.form
    // console.log(this.state.veinUrl);
    const formItemLayout = {
      labelCol: { span: 4 },
      wrapperCol: { span: 14 },
    }
    return (
        <Form onSubmit={this.handleSubmit.bind(this)}>
          <FormItem
            {...formItemLayout}
            wrapperCol={{span: 8}}
            label="滤镜分类"
          >
            {getFieldDecorator('filterType', {
              rules: [
                {required: true, message: '最多选择三个！', type: 'array',max: 3 },
              ],
            })(
              <Select mode="multiple" placeholder="请选择滤镜分类" onFocus={this.AddFilterType}>
                {
                  this.state.addFiterType.map((value,index)=>{
                    return(
                      <Option value={value.id}>{value.name}</Option>
                    )
                  })
                }
                {/* <Option value="1">1</Option> */}
              </Select>
            )}
          </FormItem>

          <Row>
            <Col span={8}>
              <FormItem
                labelCol= {{span: 12}}
                wrapperCol={{span: 12}}
                // wrapperCol={{span:5}}
                label="滤镜名称">
                {getFieldDecorator('filterName', {
                  rules: [{required: true,max: 20, message: '请输入小于20个字符的内容！' }] })
                (<Input placeholder='少于20个字符'/>)
                }
              </FormItem>
            </Col>
            <Col span={8}>
              <FormItem
                labelCol= {{span: 8}}
                wrapperCol={{span:15}}
                label="下载资费" >
                {getFieldDecorator('filterIntegral', {
                  // rules: [
                  //   { message: '请选择下载资费！' },
                  // ],
                })(
                  <Select placeholder="免费" onChange={this.handleCheckChange}
                    onFocus={this.AddFilterIntegral}
                    >
                      {
                        this.state.addIntegral.map((value,index)=>{
                          return(
                            <Option value={value.integral}>{value.integral}</Option>
                          )
                        })
                      }
                  </Select>
                )}
              </FormItem>
            </Col>
            <Col span={7} offset={1}>
              <FormItem
                {...formItemLayout}
                >
                {getFieldDecorator('vipfree', {
                  valuePropName: 'checked',
                  initialValue: true,
                })(
                  <Checkbox disabled={this.state.disabled}>VIP免费</Checkbox>
                )}
              </FormItem>
            </Col>
          </Row>

          <Row>
            <Col span={7} offset={3}>
              <FormItem
                wrapperCol={{span: 20}}
                // label="test"
              >
                {getFieldDecorator('coverImg', {
                  initialValue:this.state.coverUrl,
                  rules: [{required: true, message: '请上传封面' }, ],
                })(
                  <UploadsImg uploadButton={uploadButtonImg} beforeUpload={FileType.beforeUploadImg}
                    name={`filter/cover/admins/${this.state.userId}/${this.state.filterId}/`}
                    onChange={this.handleFilterCoverUrl} cancelUp={this.props.changeUp}
                  />
                )}
                <p style={{fontSize:12, color:"#c3c3c3"}}>* 支持扩展名：.png .gif .jpg</p>
              </FormItem>
            </Col>
            <Col span={8}>
              <FormItem
                wrapperCol={{span: 20}}
              >
                {getFieldDecorator('filterImg', {
                  initialValue:this.state.hiColorUrl,
                  rules: [{required:true,message: '请上传滤镜！'} ],
                })(
                  <UploadsFile uploadButton={uploadButtonFilter} beforeUpload={FileType.beforeUploadZIP}
                    name={`filter/hiColor/admins/${this.state.userId}/${this.state.filterId}/`}
                    onChange={this.handleFilterHiFile}
                    cancelUp={this.props.changeUp}
                  />
                )}
                <p style={{fontSize:12, color:"#c3c3c3"}}>* 支持扩展名：.zip</p>
              </FormItem>
            </Col>
            {/* <Col span={5}  offset={1}>
              <FormItem
                wrapperCol={{span: 30}}
              >
                {getFieldDecorator('veinImg', {
                  initialValue:this.state.veinUrl,
                  rules: [
                    { message: '请选择纹理！' },
                  ],
                })(
                  <UploadsImg uploadButton={uploadButtonVein} beforeUpload={FileType.beforeUploadVein}
                    name={`filter/vein/admins/${this.state.userId}/${this.state.filterId}/`}
                    onChange={this.handleFilterVeinUrl}
                    cancelUp={this.props.changeUp}
                   />
                )}
                <p style={{fontSize:12, color:"#c3c3c3"}}>支持扩展名：.png .jpg (可选)</p>
              </FormItem>
            </Col> */}
          </Row>

        <FormItem
          {...formItemLayout}
          wrapperCol={{span:5}}
          label="纹理混合"
        >
        {getFieldDecorator('textureBlend', {
          // rules: [
          //   { message: '请选择画面比例！' },
          // ],
        })(
          <Select placeholder="请选择纹理混合" onFocus={this.AddTextureBlend}
            allowClear={true}
            >
            {
              this.state.textureBlend.map((value,index)=>{
                return(
                  <Option value={value.id}>{value.name}</Option>
                )
              })
            }

          </Select>
        )}
      </FormItem>

      <FormItem
        {...formItemLayout}
        label="添加标签"
      >
        {getFieldDecorator('addTags')(
          <AddTags onChange={this.handleTagsChange}/>
        )}

      </FormItem>
      <FormItem
        {...formItemLayout}
      >
        {getFieldDecorator('filterId',{initialValue:this.state.filterId})(
          <div></div>
        )}
      </FormItem>


    </Form>

    )
  }
  componentDidMount(){
    Fetch.post({
      uri:'/api/admins/mobile/filter/addfilter',
      callback:(res)=>{
        // console.log(res);
        this.setState({
          userId:res.user_id,
          filterId:res.id
        })
      }
    })
  }
  componentWillReceiveProps(nextPorps){
    // console.log(nextPorps);
    // if(this.props.changeUp && this.props.changeUp === true){
    //   this.handleFilterCoverUrl('')
    //   this.handleFilterHiFile('')
    //   this.handleFilterVeinUrl('')
    // }
    // this.handleChangeFileList(this.props.showFile)
  }

}

const FilterMessage = Form.create()(FilterSteps)
export default FilterMessage
