import React, { Component } from 'react'
import { Steps, Button, message, Spin } from 'antd'
const Step = Steps.Step
import FilterMessage from './FilterMessage'
import ReleaseCompleted from '../../../common/completed/ReleaseCompleted'
import Fetch from 'utils/fetch'

class ReleaseSteps extends Component {
  constructor(props) {
    super(props);
    this.state = {
      current: 0,
      loading: false,
      cancelUp:false,
      finishTime:''
    }
    // console.log(this.props);
  }
  cancel(){
    this.setState({loading:true})
    const filterData = this.refs.filterFormSubmit.getFieldsValue()
    console.log(filterData);
    if(filterData.coverImg === '' && filterData.filterImg === '' && filterData.veinImg === ''){
      this.refs.filterFormSubmit.resetFields()
      setTimeout(() => {
        this.setState({ loading: false,current:0 })
        message.success('取消发布成功')
      }, 3000);
    }else{
      this.setState({
        cancelUp:true
      })
      this.refs.filterFormSubmit.resetFields()
      setTimeout(() => {
        this.setState({ loading: false,current:0 })
        message.success('清空数据成功')
      }, 3000);
    }
  }

  issue=()=>{
    this.setState({loading:true})
    const filterData = this.refs.filterFormSubmit.getFieldsValue()
    console.log(filterData);
    //分类
    if(filterData.filterType !== undefined ){
      if(filterData.filterType.length === 1 ){
        filterData.filterType = filterData.filterType.toString()
      }else if(filterData.filterType.length >1){
        filterData.filterType = filterData.filterType.join("|")
      }
    }
    //下载资费
    if(filterData.filterIntegral === undefined){
        filterData.filterIntegral = 0
    }
    //是否免费
    if(filterData.vipfree === true || filterData.vipfree===undefined || filterData.vipfree === 1 || filterData.vipfree==='true'){
      filterData.vipfree = 1
    }else{
      filterData.vipfree = 0
    }
    
    //关键字
    if(filterData.addTags.length === 0){
        filterData.addTags = ''
    }else if(filterData.addTags.length === 1 ){
      filterData.addTags = filterData.addTags.toString()
    }else if(filterData.addTags.length >1){
       filterData.addTags = filterData.addTags.join("|")
    }
    // //纹理上传
    // if(filterData.veinImg === ''){
    //
    //   filterData.veinImg='undefined'
    // }
    //纹理混合
    if(filterData.textureBlend === undefined){
      filterData.textureBlend = ''
    }

    if(filterData.coverImg === '' || filterData.filterImg === '' || filterData.filterName === undefined
      ||  filterData.filterType === undefined){
        this.setState({
          loading:false
        })
      message.error('请您进行选择！！！')
    }else{
      console.log(filterData,'4545');
      let formData = new FormData()
      formData.append('id',filterData.filterId)
      formData.append('type',filterData.filterType)
      formData.append('name',filterData.filterName)
      formData.append('integral',filterData.filterIntegral)
      formData.append('vipfree',filterData.vipfree)
      formData.append('cover',filterData.coverImg)
      formData.append('content',filterData.filterImg)
      // formData.append('texture',filterData.veinImg)
      formData.append('textMixType',filterData.textureBlend)
      formData.append('keyword',filterData.addTags)
      Fetch.post({
        uri:'/api/admins/mobile/filter/doaddfilter',
        callback:(res)=>{
          console.log(res);
          if(res.message && res.message === "发布成功"){
            setTimeout(() => {
              message.success(res.message)
              this.setState({
                loading: false,
                current:1,
                finishTime:res.finishtime
              })
            }, 2000)
          }else{
            message.error(res.message||res.error())
            this.setState({
              loading:false
            })
            console.log(res);
          }
        },
        formData:formData
      })
    }

  }

  handleReleaseBack=()=>{
    this.setState({
      current:0,
    })
  }

  render(){
    const { current } = this.state;
    const steps = [
      {
        title: '滤镜信息',
        content: (
          <div style={{paddingTop:20}}>
            <FilterMessage ref="filterFormSubmit" changeUp={this.state.cancelUp}/>
          </div> ),
      },
      {
        title:'完成发布',
        content: (<ReleaseCompleted onChange={this.handleReleaseBack} finishTime={this.state.finishTime} />),
      }]
    return(
      <Spin spinning={this.state.loading} wrapperClassName="setps_box_empty_spin">
        <div className="steps_box">
          <Steps current={current} direction="vertical" className="steps_title">
            {steps.map(item => <Step key={item.title} title={item.title} />)}
          </Steps>
          <div className="steps-content steps_content_box"> {steps[this.state.current].content}</div>
          <div className="steps-action steps_btn">
            {/* {
              this.state.current < steps.length - 1
              &&
              <Button type="primary" onClick={() => this.next()}>下一步</Button>
            }

            {
              this.state.current > 0
              &&
              <Button className="steps_btn_prev" onClick={() => this.prev()}>
                上一步
              </Button>
            } */}
            {
              this.state.current === 0
              &&
              <Button type="primary" onClick={this.issue}>发布</Button>
            }
            {
              this.state.current === 0
              &&
              <Button onClick={() => this.cancel()}>取消发布</Button>
            }

          </div>
        </div>
      </Spin>
    )
  }
}

export default ReleaseSteps
