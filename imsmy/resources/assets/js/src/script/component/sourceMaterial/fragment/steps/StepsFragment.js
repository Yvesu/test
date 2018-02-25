import React, { Component } from 'react'
import { Steps, Button, message, Spin} from 'antd'
const Step = Steps.Step
//片段 - 上传片段
import FormSteps from '../forms/FormSteps'
import StepsLast from '../steps/StepsLast'
import Fetch from 'utils/fetch'
import moment from 'moment'

class StepsFragment extends Component{
  constructor(props) {
    super(props);
    this.state = {
      current:0,
      loading: false,
      lastData:{},
      hrefCompleted:false
    }
  }

  next() {
    const current = this.state.current + 1;
    this.setState({ loading:true });
    if(this.state.current === 0){
      const store=this.refs.formSubmit.getFieldsValue()
      console.log(store,'store');

      //解析分类数组
      if(store.channel === undefined || store.channel.length===0){
         store.channel = undefined
      }else if(store.channel.length === 1 ){
        store.channel = store.channel.toString()
      }else if(store.channel.length >1){
         store.channel = store.channel.join("|")
      }
      //name
      if(store.name===null || store.name===""){
        store.name = undefined
      }
      //解析keyword数组
      if(store.keyword === undefined){
         store.keyword === undefined
      }else if(store.keyword.length === 1 ){
        store.keyword = store.keyword.toString()
      }else if(store.keyword.length >1){
         store.keyword = store.keyword.join("|")
      }
      //解析时长
      const time = moment(store.duration).format('h:m:s')
      const localTime = new Date()
      const localTimes = localTime.getHours() +":"+localTime.getMinutes()+":"+localTime.getSeconds()
      if(localTimes === time){
        store.duration===undefined
      }else{
        store.duration = moment(store.duration).format('mm:ss')
      }
      //镜头数量
      // if(store.shotNumber.length !== 0){
      //   store.shotNumber = store.shotNumber.toString()
      // }else{
      //   store.shotNumber =''
      // }

      //下载资费
      if(store.intergral=== undefined || store.intergral === 0){
        store.intergral = "0"
      }
      //是否免费
      //是否免费 1 免费 0 不免费

      if(store.vipfree === true || store.vipfree===undefined || store.vipfree === 1 || store.vipfree==='true'){
        store.vipfree = 1
      }else{
        store.vipfree = 0
      }
      //数据大小
      if(store.fileSize ===0){
        message.error('请检查您的封面(视频、片段)是否上传！！！')
        store.fileSize = undefined
      }
      //解析地址
      if(store.addressCountry === '请选择国家'){
        store.addressCountry=''
      }
      if(store.addressProvince === '请选择省份'){
        store.addressProvince=''
      }
      if(store.addressCity === '请选择城市'){
        store.addressCity=''
      }
      if(store.addressCounty === "请选择区(县)"){
        store.addressCounty=''
      }

    if(store.channel!==undefined && store.name!==undefined && store.aspect_radio!==undefined
      && store.duration!==undefined && store.shotNumber!==''  &&store.cover!==''
      && store.videoDemo!=='' && store.fileZip!=='' && store.fileSize !== undefined
        ){
          let formData = new FormData()
          formData.append('id',store.fragmentId)
          formData.append('channel',store.channel)
          formData.append('name',store.name)
          formData.append('aspect_radio',store.aspect_radio)
          formData.append('duration',store.duration)
          formData.append('storyboard_num',store.shotNumber)
          formData.append('integral',store.intergral)
          formData.append('vipfree',store.vipfree)
          formData.append('address_country',store.addressCountry)
          formData.append('address_province',store.addressProvince)
          formData.append('address_city',store.addressCity)
          formData.append('address_county',store.addressCounty)
          formData.append('address_street',store.addressStreet)
          formData.append('keyword',store.keyword)
          formData.append('cover',store.cover)
          formData.append('net_address',store.videoDemo)
          formData.append('zip_address',store.fileZip)
          formData.append('size',store.fileSize)
          Fetch.post({
            uri:'/api/admins/fodder/issue/fragment/issue',
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
          message.error('您还有必选项没有进行选择！！！')
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
      uri:'/api/admins/fodder/issue/fragment/cancel',
      callback:(res)=>{
        // console.log(res);
        if(res.message){
            setTimeout(() => {
              this.setState({
                loading: false,
                current:0
              })
              // message.error('取消发布成功！！！')
            }, 3000);
        }else{
          console.log(res.error);
        }
      },
      formData:formData
    })
  }

  issue(){
    this.setState({loading:true})
    let formData = new FormData()
    // console.log(this.state.lastData.id);
    formData.append("id",this.state.lastData.id)
    Fetch.post({
      uri:"/api/admins/fodder/issue/fragment/doissue",
      callback:(res)=>{
        console.log(res);
        if(res.message && res.message==="成功"){
          console.log(res,'chenggong');
          this.setState({
            hrefCompleted:true,
            // current:0,
            loading:false,
            finishTime:res.finishTime
          })
          message.success('发布成功')
        }else{
          console.log(res);
        }
      },
      formData:formData
    })
  }
  empty(){
    // this.setState({
    //   loading:true
    // })
    // const resetStore = this.refs.formSubmit.getFieldsValue()
    // if(resetStore.fileSize !=0 || resetStore.cover !=='' || resetStore.videoDemo !== ''){
    //
    // }else{
    //   this.refs.formSubmit.resetFields()
    // }
    this.refs.formSubmit.resetFields()

  }
  handleReleaseBack=()=>{
    this.setState({
      current:0,
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
            <FormSteps ref="formSubmit"/>
          </div> ),
      },

      {
        title:'完成发布',
        content: (<StepsLast nowData={this.state.lastData}
           completed={this.state.hrefCompleted} onChange={this.handleReleaseBack}/>)
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

export default StepsFragment
