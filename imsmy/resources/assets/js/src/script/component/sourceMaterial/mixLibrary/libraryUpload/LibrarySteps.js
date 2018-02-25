import React, { Component } from 'react'
import { Steps, Button, message, Spin} from 'antd'
const Step = Steps.Step
import moment from 'moment'
import UploadFirst from './steps/UploadFirst'
import UploadLast from './steps/UploadLast'
import Fetch from 'utils/fetch'

//混合资源库 - 上传资源
class StepsLibrary extends Component{
  constructor(props) {
    super(props);
    this.state = {
      current:0,
      loading: false,
      lastData:{},
      hrefCompleted:false,
      emptyStart:false
    }
  }

  next() {
    const current = this.state.current + 1;
    // this.setState({ current });
    if(this.state.current === 0){
      const store=this.refs.formSubmit.getFieldsValue()
      console.log(store,'store');

      //name
      if(store.name===null || store.name===""){
        store.name = undefined
      }
      //解析keyword数组
      if(store.keyword === undefined){
         store.keyword = ''
      }else if(store.keyword.length && store.keyword.length===0 ){
        store.keyword = ''
      }else if(store.keyword.length === 1 ){
        store.keyword = store.keyword.toString()
      }else if(store.keyword.length >1){
         store.keyword = store.keyword.join("|")
      }
      //解析时长
      const time = moment(store.duration).format('h:m:s')
      const initTime = moment(store.duration).format('mm:ss')
      const localTime = new Date()
      const localTimes = localTime.getHours() +":"+localTime.getMinutes()+":"+localTime.getSeconds()
      if(localTimes === time){
        store.duration = undefined
      }else if(initTime=== '00:00'){
        store.duration = undefined
      }else{
        store.duration = moment(store.duration).format('mm:ss')
      }
      //分辨率
      if(store.widths ===0 || store.heights ===0){
        store.widths = undefined
        store.heights = undefined
      }
      //是否带Alpha 1 是   0 不是
      if(store.Alpha === true || store.Alpha===undefined || store.Alpha === 1 || store.Alpha==='true'){
        store.Alpha = 1
      }else{
        store.Alpha = 0
      }
      //下载资费
      if(store.intergral=== undefined || store.intergral === 0 ||store.intergral=== '免费'){
        store.intergral = "0"
      }
      //是否免费 1 免费 0 不免费
      // console.log(store.vipfree ,'vipfree');
      if(store.vipfree === true || store.vipfree===undefined || store.vipfree === 1 || store.vipfree==='true'){
        store.vipfree = 1
      }else{
        store.vipfree = 0
      }

      //数据大小
      if(store.fileSize ===undefined || store.cover===undefined ||store.videoDemo===undefined || store.fileZip===undefined){
        message.error('请检查您的封面(视频、文件)是否上传！！！')
      }
      if(store.fileSize ===0 || store.cover===null ||store.videoDemo===null || store.fileZip===null){
          message.error('请检查您的封面(视频、文件)是否上传！！！')
          store.fileSize = undefined
          store.cover = undefined
          store.videoDemo = undefined
          store.fileZip = undefined
      }

    if(store.type!==undefined && store.name!==undefined && store.duration!==undefined && store.widths !== undefined
      && store.heights !==undefined &&store.cover!==undefined && store.videoDemo!==undefined && store.fileZip!==undefined
      && store.fileSize !== undefined
        ){
          let formData = new FormData()
          formData.append('id',store.libraryId)
          formData.append('type_id',store.type)
          formData.append('name',store.name)
          formData.append('integral',store.intergral)
          formData.append('vipfree',store.vipfree)
          formData.append('duration',store.duration)
          formData.append('distinguishability_x',store.widths)
          formData.append('distinguishability_y',store.heights)
          formData.append('isalpha',store.Alpha)
          if(store.keyword !== ''){
            formData.append('keywords',store.keyword)
          }
          formData.append('cover',store.cover)
          formData.append('preview_address',store.videoDemo)
          formData.append('address',store.fileZip)
          formData.append('size',store.fileSize)
          Fetch.post({
            uri:'/api/admins/fodder/mixresource/issueplay',
            callback:(res)=>{
              console.log(res,'fanhui');
              if(res.data.user_id){
                this.setState({
                  current,
                  lastData:res.data,
                  loading:false
                });
              }else{
                console.log(res,"error");
                this.setState({
                  loading:false
                })
              }
            },
            formData:formData
          })
        }else{
          message.error('请进行选择')
          this.setState({
            loading:false
          })
        }
    }

  }

  prev() {
    this.setState({loading:true})
    const current = this.state.current - 1;
    this.setState({ current,loading:false });
  }

  cancel(){
    this.setState({loading:true})
    let formData = new FormData()
    formData.append('id',this.state.lastData.id)
    Fetch.post({
      uri:'/api/admins/fodder/mixresource/cancelissue',
      callback:(res)=>{
        console.log(res,'取消发布');
        if(res.message && res.message==='取消成功'){
            setTimeout(() => {
              this.setState({
                loading: false,
                current:0
              })
              message.success(res.message)
            }, 3000);
        }else{
          console.log(res);
          message.error(res.message || res.error)
          this.setState({
            loading:false
          })
        }
      },
      formData:formData
    })
  }

  issue(){
    this.setState({loading:true})
    let formData = new FormData()
    formData.append("id",this.state.lastData.id)
    Fetch.post({
      uri:"/api/admins/fodder/mixresource/doissue",
      callback:(res)=>{
        console.log(res,'chenggong');
        if(res.message && res.message==="成功"){
          console.log(res,'chenggong');
          this.setState({
            hrefCompleted:true,
            // current:0,
            loading:false,
            finishTime:res.finishTime
          })
          message.success(res.message)
        }else{
          this.setState({
            loading:false,
          })
          message.error(res.message || res.error);
          // console.log(res);
        }
      },
      formData:formData
    })
  }
  empty(){
    this.setState({loading:true})
    const emptyForm=this.refs.formSubmit.getFieldsValue()
    if(emptyForm.cover === null && emptyForm.videoDemo === null && emptyForm.fileZip === null){
      this.refs.formSubmit.resetFields()
      this.setState({
        loading:false,
        emptyStart:true
      })
    }else{
      const cancelId=this.refs.formSubmit.getFieldValue('libraryId')
      let formData = new FormData()
      formData.append('id',cancelId)
      Fetch.post({
        uri:'/api/admins/fodder/mixresource/clear',
        callback:(res)=>{
          console.log(res,'清空');
          if(res.message && res.message==='清空成功'){
            setTimeout(() => {
              this.setState({
                loading: false,
                current:0,
                emptyStart:true
              })
              message.success(res.message)
            }, 3000);
          }else{
            console.log(res);
            message.error(res.message || res.error)
            this.setState({
              loading:false
            })
          }
        },
        formData:formData
      })
    }

  }
  handleReleaseBack=()=>{
    this.setState({
      current:0,
      hrefCompleted:false
    })
  }
  render(){
    // console.log(this.state.cancelEmpty,'ces');
    const { current } = this.state;
    const steps = [
      {
        title: '基本信息',
        content: (
          <div style={{paddingTop:20}}>
            <UploadFirst ref="formSubmit" isEmptyForm={this.state.emptyStart}/>
          </div> ),
      },

      {
        title:'完成发布',
        content: (<UploadLast nowData={this.state.lastData}
           completed={this.state.hrefCompleted} onChange={this.handleReleaseBack} />)
      }]
    return (
      <Spin spinning={this.state.loading} wrapperClassName="setps_box_empty_spin">
        <div className="steps_box">
          <Steps current={current} direction="vertical" className="steps_title">
            {steps.map(item => <Step key={item.title} title={item.title} />)}
          </Steps>
          <div className="steps-content steps_content_box"> {steps[this.state.current].content}</div>
          <div className="steps-action steps_btn">
            {
              this.state.current < steps.length - 1
              &&
              <Button type="primary" onClick={() => this.next()}>下一步</Button>
            }
            {
              this.state.current < steps.length - 1
              &&
              <Button onClick={() => this.empty()}>清空</Button>
            }

            {
              this.state.current > 0
              &&
              <Button className="steps_btn_prev" onClick={() => this.prev()}>
                上一步
              </Button>
            }
            {
              this.state.current === steps.length - 1
              &&
              <Button type="primary" onClick={() =>this.issue()}>发布</Button>
            }
            {
              this.state.current === steps.length - 1
              &&
              <Button onClick={() => this.cancel()}>
                取消发布
              </Button>
            }
          </div>
        </div>
      </Spin>
    )
  }

}

export default StepsLibrary
