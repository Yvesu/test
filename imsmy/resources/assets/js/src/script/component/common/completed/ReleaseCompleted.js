import React, { Component } from 'react'
import { Steps, Button } from 'antd'
const Step = Steps.Step
//finishTime  完成发布时间
class ReleaseCompleted extends Component{
  constructor(props){
    super(props)
    this.state={
      time:''
    }
  }
  handleChangeRelease=()=>{
    if(this.props.onChange){
      this.props.onChange()
    }
  }
  render(){
    return(
      <div className="release_completed_box">
        <Steps current={1} size="small" className="change_steps_box">
          <Step title="发布完成" description={this.state.time} />
          <Step title="正在处理" description="等待测试" />
          <Step title="审核通过" description="上线使用" />
        </Steps>
        <Button type='primary' className="release_btn_completed"
          onClick={this.handleChangeRelease}>
            继续发布
        </Button>
      </div>
    )
  }
  componentDidMount(){
    if(this.props.finishTime && this.props.finishTime !== undefined){
      this.setState({
        time:this.props.finishTime
      })
    }
  }
}

export default ReleaseCompleted
